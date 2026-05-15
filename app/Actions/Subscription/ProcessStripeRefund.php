<?php

namespace App\Actions\Subscription;

use App\Models\CreditLog;
use App\Models\PurchaseTransaction;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class ProcessStripeRefund
{
    public function execute(PurchaseTransaction $transaction): void
    {
        if ($transaction->status !== 'paid') {
            throw new \RuntimeException('Only paid transactions can be refunded.');
        }

        $siteSetting = SiteSetting::first();

        if (! $siteSetting?->stripe_enabled || ! $siteSetting->stripe_secret_key) {
            throw new \RuntimeException('Stripe is not configured.');
        }

        if (! $transaction->stripe_payment_intent_id) {
            throw new \RuntimeException('No Stripe payment intent found for this transaction.');
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

        $fullAmountInCents = $this->toStripeAmountInCents((float) $transaction->amount);

        $stripe = new StripeClient($siteSetting->stripe_secret_key);

        $refundPayload = [
            'payment_intent' => $transaction->stripe_payment_intent_id,
        ];

        if ($refundAmountInCents < $fullAmountInCents) {
            $refundPayload['amount'] = $refundAmountInCents;
        }

        $refund = $stripe->refunds->create($refundPayload);

        Log::info('Stripe refund created', [
            'refund_id' => $refund->id,
            'transaction_id' => $transaction->id,
            'amount' => $refundAmountInCents / 100,
            'refundable_credits' => $refundableCredits,
        ]);

        DB::transaction(function () use ($transaction, $refundableCredits): void {
            $locked = PurchaseTransaction::lockForUpdate()->find($transaction->id);
            $creditsToDeduct = 0;

            if (! $locked || $locked->status !== 'paid') {
                throw new \RuntimeException('Transaction is no longer eligible for refund.');
            }

            $user = $locked->user;
            if ($user) {
                $availableCredits = max((int) $user->credits, 0);
                $creditsToDeduct = min($availableCredits, $refundableCredits);

                if ($creditsToDeduct <= 0) {
                    throw new \RuntimeException('No refundable balance remains to deduct from the wallet.');
                }

                if ($creditsToDeduct < $refundableCredits) {
                    Log::warning('Refund credit deduction exceeds available credits', [
                        'transaction_id' => $locked->id,
                        'user_id' => $user->id,
                        'credits_to_deduct' => $refundableCredits,
                        'credits_available' => $availableCredits,
                    ]);
                }

                $user->decrement('credits', $creditsToDeduct);
            }

            $locked->update(['status' => 'refunded']);

            Log::info('Transaction refunded and credits deducted', [
                'transaction_id' => $locked->id,
                'user_id' => $user?->id,
                'credits_deducted' => $creditsToDeduct,
            ]);
        });
    }

    /**
     * @return array{transaction_credits: int, used_credits: int, unused_credits: int, refundable_credits: int}
     */
    private function calculateRefundBreakdown(PurchaseTransaction $transaction): array
    {
        $transactionCredits = max((int) $transaction->credits, 0);
        $userId = $transaction->user_id;

        if (! $userId || $transactionCredits <= 0) {
            return [
                'transaction_credits' => $transactionCredits,
                'used_credits' => 0,
                'unused_credits' => 0,
                'refundable_credits' => 0,
            ];
        }

        $totalUsedCredits = (int) abs(CreditLog::query()
            ->where('user_id', $userId)
            ->where('amount', '<', 0)
            ->sum('amount'));

        $transactionSortDate = $transaction->paid_at ?? $transaction->created_at;

        $paidTransactions = PurchaseTransaction::query()
            ->where('user_id', $userId)
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
            ->get(['id', 'credits']);

        $remainingUsedCredits = $totalUsedCredits;
        $usedCreditsFromTransaction = 0;

        foreach ($paidTransactions as $paidTransaction) {
            $paidCredits = max((int) $paidTransaction->credits, 0);
            $allocatedUsedCredits = min($remainingUsedCredits, $paidCredits);

            if ((int) $paidTransaction->id === (int) $transaction->id) {
                $usedCreditsFromTransaction = $allocatedUsedCredits;
                break;
            }

            $remainingUsedCredits -= $allocatedUsedCredits;
        }

        $unusedCredits = max($transactionCredits - $usedCreditsFromTransaction, 0);
        $transaction->loadMissing('user:id,credits');

        $availableCredits = max((int) ($transaction->user?->credits ?? 0), 0);

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
