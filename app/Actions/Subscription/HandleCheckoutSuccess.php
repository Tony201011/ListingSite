<?php

namespace App\Actions\Subscription;

use App\Models\PurchaseTransaction;
use App\Services\Payments\PaymentProviderManager;
use Illuminate\Support\Facades\Auth;

class HandleCheckoutSuccess
{
    public function __construct(
        private PaymentProviderManager $paymentProviderManager,
        private FinalizeCreditPurchase $finalizeCreditPurchase,
    ) {}

    /**
     * @return array{status: string, credits?: int, error?: string}
     */
    public function execute(string $sessionId): array
    {
        $transaction = PurchaseTransaction::query()
            ->where(function ($query) use ($sessionId): void {
                $query->where('provider_checkout_id', $sessionId)
                    ->orWhere('stripe_session_id', $sessionId);
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
            $session = $provider->retrieveCheckout($sessionId);

            if ($session->payment_status !== 'paid') {
                return ['status' => 'unpaid'];
            }

            $transaction = $this->finalizeCreditPurchase->execute($transaction, [
                'provider_checkout_id' => $session->id,
                'provider_transaction_id' => is_string($session->payment_intent)
                    ? $session->payment_intent
                    : $session->payment_intent?->id,
                'receipt_url' => $session->payment_intent?->latest_charge?->receipt_url ?? null,
                'paid_at' => now(),
            ]);

            return ['status' => 'paid', 'credits' => $transaction->total_credits];
        } catch (\Exception $e) {
            return ['status' => 'error', 'error' => $e->getMessage()];
        }
    }
}
