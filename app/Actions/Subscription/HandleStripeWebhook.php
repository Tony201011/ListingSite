<?php

namespace App\Actions\Subscription;

use App\Models\CreditLog;
use App\Models\PurchaseTransaction;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

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

        $receiptUrl = $this->fetchReceiptUrl($session->payment_intent ?? null);

        DB::transaction(function () use ($session, $transaction, $receiptUrl) {
            $locked = PurchaseTransaction::lockForUpdate()->find($transaction->id);

            if (! $locked || $locked->status === 'paid') {
                return;
            }

            $locked->update([
                'status' => 'paid',
                'stripe_payment_intent_id' => $session->payment_intent,
                'receipt_url' => $receiptUrl,
                'paid_at' => now(),
            ]);

            $user = $locked->user;
            if ($user) {
                $user->increment('credits', $locked->credits);
                CreditLog::create([
                    'user_id' => $user->id,
                    'amount' => $locked->credits,
                    'type' => 'purchase_credit',
                    'description' => "Purchased {$locked->credits} credits",
                    'reference_type' => PurchaseTransaction::class,
                    'reference_id' => $locked->id,
                ]);
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

    private function fetchReceiptUrl(?string $paymentIntentId): ?string
    {
        if (! $paymentIntentId) {
            return null;
        }

        try {
            $siteSetting = SiteSetting::first();
            if (! $siteSetting?->stripe_enabled || ! $siteSetting->stripe_secret_key) {
                return null;
            }

            $stripe = new StripeClient($siteSetting->stripe_secret_key);
            $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId, [
                'expand' => ['latest_charge'],
            ]);

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
