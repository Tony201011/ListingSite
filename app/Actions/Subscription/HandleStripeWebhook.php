<?php

namespace App\Actions\Subscription;

use App\Models\PurchaseTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HandleStripeWebhook
{
    public function __construct(
        private SendCreditPurchaseEmail $sendCreditPurchaseEmail,
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

        DB::transaction(function () use ($session, $transaction) {
            $locked = PurchaseTransaction::lockForUpdate()->find($transaction->id);

            if (! $locked || $locked->status === 'paid') {
                return;
            }

            $locked->update([
                'status' => 'paid',
                'stripe_payment_intent_id' => $session->payment_intent,
                'paid_at' => now(),
            ]);

            $user = $locked->user;
            if ($user) {
                $user->increment('credits', $locked->credits);
                Log::info('Credits added to user via webhook', [
                    'user_id' => $user->id,
                    'credits_added' => $locked->credits,
                    'transaction_id' => $locked->id,
                    'session_id' => $session->id,
                ]);
            }
        });

        $transaction->refresh();

        if ($transaction->status === 'paid') {
            $this->sendCreditPurchaseEmail->execute($transaction);
        }
    }

    private function handlePaymentIntentSucceeded(object $paymentIntent): void
    {
        Log::info('Payment intent succeeded', ['id' => $paymentIntent->id]);
    }
}
