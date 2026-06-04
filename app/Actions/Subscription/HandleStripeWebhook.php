<?php

namespace App\Actions\Subscription;

use App\Models\PurchaseTransaction;
use Illuminate\Support\Facades\Log;

class HandleStripeWebhook
{
    public function __construct(
        private FinalizeCreditPurchase $finalizeCreditPurchase,
    ) {}

    public function execute(object $event): void
    {
        Log::info('Stripe webhook received', ['type' => $event->type, 'id' => $event->id]);

        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event->data->object);
                break;
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event->data->object);
                break;
            default:
                Log::info('Unhandled Stripe event type', ['type' => $event->type]);
        }
    }

    private function handleCheckoutSessionCompleted(object $session): void
    {
        if ($session->payment_status !== 'paid') {
            return;
        }

        $transactionId = $session->metadata->transaction_id ?? null;

        if (! $transactionId) {
            Log::warning('Stripe webhook: checkout.session.completed received without transaction_id', [
                'session_id' => $session->id,
            ]);

            return;
        }

        $transaction = PurchaseTransaction::find($transactionId);

        if (! $transaction || $transaction->status === 'paid') {
            return;
        }

        $transaction = $this->finalizeCreditPurchase->execute($transaction, [
            'provider_checkout_id' => $session->id,
            'provider_transaction_id' => (string) ($session->payment_intent ?? ''),
            'receipt_url' => $this->fetchReceiptUrl($session->payment_intent ?? null),
            'paid_at' => now(),
        ]);

        Log::info('Wallet credited from webhook', [
            'user_id' => $transaction->user_id,
            'provider_profile_id' => $transaction->provider_profile_id,
            'credits_added' => $transaction->total_credits,
            'transaction_id' => $transaction->id,
            'session_id' => $session->id,
        ]);
    }

    private function handlePaymentIntentSucceeded(object $paymentIntent): void
    {
        Log::info('Payment intent succeeded', ['id' => $paymentIntent->id]);

        $transaction = PurchaseTransaction::where('stripe_payment_intent_id', $paymentIntent->id)->first();

        if (! $transaction || $transaction->status === 'paid') {
            return;
        }

        $transaction = $this->finalizeCreditPurchase->execute($transaction, [
            'provider_transaction_id' => $paymentIntent->id,
            'receipt_url' => $paymentIntent->latest_charge?->receipt_url ?? null,
            'paid_at' => now(),
        ]);

        Log::info('Wallet credited from payment intent webhook', [
            'user_id' => $transaction->user_id,
            'provider_profile_id' => $transaction->provider_profile_id,
            'credits_added' => $transaction->total_credits,
            'transaction_id' => $transaction->id,
            'payment_intent_id' => $paymentIntent->id,
        ]);
    }

    private function fetchReceiptUrl(?string $paymentIntentId): ?string
    {
        if (! $paymentIntentId) {
            return null;
        }

        try {
            $paymentIntent = app(\App\Services\Payments\PaymentProviderManager::class)
                ->for('stripe')
                ->retrievePaymentIntent($paymentIntentId);

            return $paymentIntent->latest_charge?->receipt_url ?? null;
        } catch (\Exception $e) {
            Log::warning('Failed to fetch Stripe receipt URL', [
                'payment_intent_id' => $paymentIntentId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
