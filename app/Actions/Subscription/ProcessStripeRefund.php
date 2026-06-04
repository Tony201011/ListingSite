<?php

namespace App\Actions\Subscription;

use App\Actions\Referral\ReverseReferralRewardForRefund;
use App\Models\Refund;
use App\Models\PurchaseTransaction;
use App\Services\Payments\PaymentProviderManager;
use App\Services\WalletLedgerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessStripeRefund
{
    public function __construct(
        private ReverseReferralRewardForRefund $reverseReferralRewardForRefund,
        private PaymentProviderManager $paymentProviderManager,
        private WalletLedgerService $walletLedgerService,
    ) {}

    public function execute(PurchaseTransaction $transaction): void
    {
        if ($transaction->status !== 'paid') {
            throw new \RuntimeException('Only paid transactions can be refunded.');
        }

        $provider = $this->paymentProviderManager->for($transaction->provider ?: 'stripe');

        if (! $provider->isConfigured()) {
            throw new \RuntimeException('Payment provider is not configured.');
        }

        if (! ($transaction->provider_transaction_id ?: $transaction->stripe_payment_intent_id)) {
            throw new \RuntimeException('No payment reference found for this transaction.');
        }

        $refundBreakdown = $this->calculateRefundBreakdown($transaction);
        $refundableCredits = $refundBreakdown['refundable_credits'];

        if ($refundableCredits <= 0) {
            throw new \RuntimeException('No refundable balance is available for this transaction.');
        }

        $refundAmountInCents = $this->calculateRefundAmountInCents(
            (float) $transaction->amount,
            $refundBreakdown['transaction_credits'],
            $refundableCredits
        );

        if ($refundAmountInCents <= 0) {
            throw new \RuntimeException('Unable to calculate a refundable amount for this transaction.');
        }

        $refund = $provider->refund($transaction, $refundAmountInCents);

        Log::info('Payment refund created', [
            'refund_id' => $refund->id,
            'transaction_id' => $transaction->id,
            'amount' => $refundAmountInCents / 100,
            'refundable_credits' => $refundableCredits,
        ]);

        DB::transaction(function () use ($transaction, $refundableCredits, $refundAmountInCents, $refund): void {
            $locked = PurchaseTransaction::lockForUpdate()->find($transaction->id);
            $creditsToDeduct = 0;

            if (! $locked || $locked->status !== 'paid') {
                throw new \RuntimeException('Transaction is no longer eligible for refund.');
            }

            $profile = $locked->providerProfile;
            if ($profile) {
                $availableCredits = max($this->walletLedgerService->currentBalance($profile), 0);
                $creditsToDeduct = min($availableCredits, $refundableCredits);

                if ($creditsToDeduct <= 0) {
                    throw new \RuntimeException('No refundable balance remains to deduct from the wallet.');
                }

                if ($creditsToDeduct < $refundableCredits) {
                    Log::warning('Refund credit deduction exceeds available credits', [
                        'transaction_id' => $locked->id,
                        'user_id' => $locked->user_id,
                        'provider_profile_id' => $profile->id,
                        'credits_to_deduct' => $refundableCredits,
                        'credits_available' => $availableCredits,
                    ]);
                }

                $this->walletLedgerService->record(
                    $profile,
                    -$creditsToDeduct,
                    'refund',
                    "Refund processed for payment #{$locked->id}",
                    $locked,
                    'refund',
                );
            }

            $locked->update(['status' => 'refunded']);

            Refund::query()->create([
                'payment_id' => $locked->id,
                'user_id' => $locked->user_id,
                'amount' => $refundAmountInCents / 100,
                'refunded_credits' => $creditsToDeduct,
                'provider_refund_id' => $refund->id ?? null,
                'reason' => 'Unused wallet credits refunded by admin.',
                'status' => 'completed',
            ]);

            $this->reverseReferralRewardForRefund->execute($locked);

            Log::info('Transaction refunded and credits deducted', [
                'transaction_id' => $locked->id,
                'user_id' => $locked->user_id,
                'provider_profile_id' => $locked->provider_profile_id,
                'credits_deducted' => $creditsToDeduct,
            ]);
        });
    }

    /**
     * @return array{transaction_credits: int, used_credits: int, unused_credits: int, refundable_credits: int}
     */
    private function calculateRefundBreakdown(PurchaseTransaction $transaction): array
    {
        $transactionCredits = max((int) $transaction->total_credits, 0);
        $userId = $transaction->user_id;
        $profileId = $transaction->provider_profile_id;

        if (! $userId || ! $profileId || $transactionCredits <= 0) {
            return [
                'transaction_credits' => $transactionCredits,
                'used_credits' => 0,
                'unused_credits' => 0,
                'refundable_credits' => 0,
            ];
        }

        $totalUsedCredits = (int) abs(\App\Models\CreditLog::query()
            ->where('user_id', $userId)
            ->where('reference_type', \App\Models\ProviderProfile::class)
            ->where('reference_id', $profileId)
            ->where('amount', '<', 0)
            ->where('type', '!=', 'refund')
            ->sum('amount'));

        $transactionSortDate = $transaction->paid_at ?? $transaction->created_at;

        $paidTransactions = PurchaseTransaction::query()
            ->where('user_id', $userId)
            ->where('provider_profile_id', $profileId)
            ->where('status', 'paid')
            ->where(function ($query) use ($transactionSortDate, $transaction): void {
                $query->whereRaw('COALESCE(paid_at, created_at) < ?', [$transactionSortDate])
                    ->orWhere(function ($nestedQuery) use ($transactionSortDate, $transaction): void {
                        $nestedQuery
                            ->whereRaw('COALESCE(paid_at, created_at) = ?', [$transactionSortDate])
                            ->where('id', '<=', $transaction->id);
                    });
            })
            ->orderByRaw('COALESCE(paid_at, created_at) ASC')
            ->orderBy('id')
            ->get(['id', 'credits', 'bonus_credits']);

        $remainingUsedCredits = $totalUsedCredits;
        $usedCreditsFromTransaction = 0;

        foreach ($paidTransactions as $paidTransaction) {
            $paidCredits = max((int) $paidTransaction->total_credits, 0);
            $allocatedUsedCredits = min($remainingUsedCredits, $paidCredits);

            if ((int) $paidTransaction->id === (int) $transaction->id) {
                $usedCreditsFromTransaction = $allocatedUsedCredits;
                break;
            }

            $remainingUsedCredits -= $allocatedUsedCredits;
        }

        $unusedCredits = max($transactionCredits - $usedCreditsFromTransaction, 0);
        $transaction->loadMissing('providerProfile:id,credits');
        $availableCredits = max(
            $transaction->providerProfile
                ? $this->walletLedgerService->currentBalance($transaction->providerProfile)
                : 0,
            0
        );

        return [
            'transaction_credits' => $transactionCredits,
            'used_credits' => $usedCreditsFromTransaction,
            'unused_credits' => $unusedCredits,
            'refundable_credits' => min($unusedCredits, $availableCredits),
        ];
    }

    private function calculateRefundAmountInCents(float $transactionAmount, int $transactionCredits, int $refundableCredits): int
    {
        $fullAmountInCents = $this->toStripeAmountInCents($transactionAmount);

        if ($fullAmountInCents <= 0 || $transactionCredits <= 0 || $refundableCredits <= 0) {
            return 0;
        }

        if ($refundableCredits >= $transactionCredits) {
            return $fullAmountInCents;
        }

        return max(
            0,
            min(
                $fullAmountInCents,
                (int) round(($fullAmountInCents * $refundableCredits) / $transactionCredits)
            )
        );
    }

    private function toStripeAmountInCents(float $amount): int
    {
        return max((int) round($amount * 100), 0);
    }
}
