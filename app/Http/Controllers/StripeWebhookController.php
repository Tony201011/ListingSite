<?php

namespace App\Http\Controllers;

use App\Actions\Subscription\HandleStripeWebhook;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __construct(
        private HandleStripeWebhook $handleStripeWebhook,
    ) {}

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

        $this->handleStripeWebhook->execute($event);

        return response('OK', 200);
    }
}
