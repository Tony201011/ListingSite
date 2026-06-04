<?php

namespace App\Actions\Subscription;

use App\Models\PurchaseTransaction;
use App\Services\Payments\PaymentProviderManager;
use Illuminate\Support\Facades\Auth;

class HandlePaymentIntentSuccess
{
    public function __construct(
        private PaymentProviderManager $paymentProviderManager,
        private FinalizeCreditPurchase $finalizeCreditPurchase,
    ) {}

    /**
     * @return array{status: string, credits?: int, error?: string}
     */
    public function execute(string $paymentIntentId): array
    {
        $transaction = PurchaseTransaction::query()
            ->where(function ($query) use ($paymentIntentId): void {
                $query->where('provider_transaction_id', $paymentIntentId)
                    ->orWhere('stripe_payment_intent_id', $paymentIntentId);
            })
            ->where('user_id', Auth::id())
            ->first();

        if (! $transaction) {
            return ['status' => 'not_found'];
        }

        if ($transaction->status === 'paid') {
            return ['status' => 'paid', 'credits' => $transaction->total_credits];
        }

        $provider = $this->paymentProviderManager->current();

        if (! $provider->isConfigured()) {
            return ['status' => 'not_configured'];
        }

        try {
            $paymentIntent = $provider->retrievePaymentIntent($paymentIntentId);

            if ($paymentIntent->status !== 'succeeded') {
                return ['status' => 'unpaid'];
            }

            $transaction = $this->finalizeCreditPurchase->execute($transaction, [
                'provider_transaction_id' => $paymentIntentId,
                'receipt_url' => $paymentIntent->latest_charge?->receipt_url ?? null,
                'paid_at' => now(),
            ]);

            return ['status' => 'paid', 'credits' => $transaction->total_credits];
        } catch (\Exception $e) {
            return ['status' => 'error', 'error' => $e->getMessage()];
        }
    }
}
