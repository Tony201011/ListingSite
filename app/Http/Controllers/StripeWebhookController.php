<?php

namespace App\Http\Controllers;

use App\Models\PurchaseTransaction;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $siteSetting = SiteSetting::first();

        if (! $siteSetting?->stripe_enabled || ! $siteSetting->stripe_webhook_secret) {
            Log::warning('Stripe webhook received but Stripe is not configured');

            return response('Stripe not configured', 400);
        }

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $siteSetting->stripe_webhook_secret);
        } catch (\UnexpectedValueException $e) {
            Log::error('Invalid Stripe payload', ['error' => $e->getMessage()]);

            return response('Invalid payload', 400);
        } catch (SignatureVerificationException $e) {
            Log::error('Invalid Stripe signature', ['error' => $e->getMessage()]);

            return response('Invalid signature', 400);
        }

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

        return response('OK', 200);
    }

    private function handleCheckoutSessionCompleted($session)
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

        DB::transaction(function () use ($session, $transactionId) {
            $transaction = PurchaseTransaction::lockForUpdate()->find($transactionId);

            if (! $transaction || $transaction->status === 'paid') {
                return;
            }

            $transaction->update([
                'status' => 'paid',
                'stripe_payment_intent_id' => $session->payment_intent,
                'paid_at' => now(),
            ]);

            $user = $transaction->user;
            if ($user) {
                $user->increment('credits', $transaction->credits);
                Log::info('Credits added to user via webhook', [
                    'user_id' => $user->id,
                    'credits_added' => $transaction->credits,
                    'transaction_id' => $transaction->id,
                    'session_id' => $session->id,
                ]);
            }
        });
    }

    private function handlePaymentIntentSucceeded($paymentIntent)
    {
        // Handle payment intent succeeded if needed
        Log::info('Payment intent succeeded', ['id' => $paymentIntent->id]);
    }
}
