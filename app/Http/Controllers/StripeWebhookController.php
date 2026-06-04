<?php

namespace App\Http\Controllers;

use App\Actions\Subscription\HandleStripeWebhook;
use App\Services\Payments\PaymentProviderManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    public function __construct(
        private HandleStripeWebhook $handleStripeWebhook,
        private PaymentProviderManager $paymentProviderManager,
    ) {}

    public function handleWebhook(Request $request)
    {
        $provider = $this->paymentProviderManager->for('stripe');

        if (! $provider->isConfigured()) {
            Log::warning('Payment webhook received but Stripe is not configured');

            return response('Stripe not configured', 400);
        }

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = $provider->constructWebhookEvent($payload, $sigHeader);
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
