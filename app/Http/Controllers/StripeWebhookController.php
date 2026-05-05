<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $siteSetting = SiteSetting::first();

        if (!$siteSetting?->stripe_enabled || !$siteSetting->stripe_webhook_secret) {
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
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
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
        if ($session->payment_status === 'paid') {
            $userId = $session->metadata->user_id ?? null;
            $credits = $session->metadata->credits ?? null;

            if ($userId && $credits) {
                $user = User::find($userId);
                if ($user) {
                    $user->increment('credits', (int) $credits);
                    Log::info('Credits added to user', [
                        'user_id' => $userId,
                        'credits_added' => $credits,
                        'session_id' => $session->id
                    ]);
                }
            }
        }
    }

    private function handlePaymentIntentSucceeded($paymentIntent)
    {
        // Handle payment intent succeeded if needed
        Log::info('Payment intent succeeded', ['id' => $paymentIntent->id]);
    }
}
