<?php

namespace App\Actions\Subscription;

use App\Actions\Referral\ProcessReferralRewardForFirstPayment;
use App\Models\ProviderProfile;
use App\Models\PurchaseTransaction;
use App\Services\WalletLedgerService;
use Illuminate\Support\Facades\DB;

class FinalizeCreditPurchase
{
    public function __construct(
        private WalletLedgerService $walletLedgerService,
        private CreateInvoiceForPayment $createInvoiceForPayment,
        private SendCreditPurchaseEmail $sendCreditPurchaseEmail,
        private ProcessReferralRewardForFirstPayment $processReferralRewardForFirstPayment,
    ) {}

    /**
     * @param  array{provider_transaction_id?: ?string, provider_checkout_id?: ?string, receipt_url?: ?string, paid_at?: mixed}  $attributes
     */
    public function execute(PurchaseTransaction $payment, array $attributes = []): PurchaseTransaction
    {
        DB::transaction(function () use ($payment, $attributes): void {
            $locked = PurchaseTransaction::query()
                ->with('providerProfile')
                ->lockForUpdate()
                ->findOrFail($payment->id);

            if ($locked->status === 'paid') {
                return;
            }

            $providerTransactionId = $attributes['provider_transaction_id'] ?? $locked->provider_transaction_id ?? $locked->stripe_payment_intent_id;
            $providerCheckoutId = $attributes['provider_checkout_id'] ?? $locked->provider_checkout_id ?? $locked->stripe_session_id;

            $locked->update([
                'status' => 'paid',
                'provider_transaction_id' => $providerTransactionId,
                'provider_checkout_id' => $providerCheckoutId,
                'stripe_payment_intent_id' => $locked->provider === 'stripe' ? $providerTransactionId : $locked->stripe_payment_intent_id,
                'stripe_session_id' => $locked->provider === 'stripe' ? $providerCheckoutId : $locked->stripe_session_id,
                'receipt_url' => $attributes['receipt_url'] ?? $locked->receipt_url,
                'paid_at' => $attributes['paid_at'] ?? now(),
            ]);

            $profile = $locked->providerProfile;

            if (! $profile instanceof ProviderProfile) {
                return;
            }

            $creditedAmount = (int) $locked->credits + (int) $locked->bonus_credits;

            $this->walletLedgerService->record(
                $profile,
                $creditedAmount,
                'purchase_credit',
                "Purchased {$creditedAmount} credits via ".str($locked->provider)->headline()->lower(),
                $locked,
                'purchase',
            );

            $this->createInvoiceForPayment->execute($locked->fresh(['creditPackage']));
            $this->processReferralRewardForFirstPayment->execute($locked);
        });

        $payment->refresh();
        $this->sendCreditPurchaseEmail->execute($payment);

        return $payment;
    }
}
