<?php

namespace App\Services\Payments;

use App\Models\PurchaseTransaction;
use App\Models\SiteSetting;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;

class StripePaymentProvider implements PaymentProviderInterface
{
    public function name(): string
    {
        return 'stripe';
    }

    public function isConfigured(): bool
    {
        $settings = $this->settings();
        $stripeMode = $settings?->stripe_mode ?: 'sandbox';

        return (bool) (
            $settings?->stripe_enabled
            && $settings?->stripe_secret_key
            && in_array($stripeMode, ['sandbox', 'live'], true)
        );
    }

    public function publicKey(): ?string
    {
        return $this->settings()?->stripe_publishable_key;
    }

    public function createCheckout(PurchaseTransaction $payment, array $lineItem, array $metadata, string $customerEmail): array
    {
        $session = $this->client()->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items' => [$lineItem],
            'mode' => 'payment',
            'success_url' => route('purchase-credit.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('purchase-credit'),
            'metadata' => $metadata,
            'customer_email' => $customerEmail,
        ]);

        return [
            'id' => $session->id,
            'url' => $session->url,
        ];
    }

    public function createPaymentIntent(PurchaseTransaction $payment, int $amountInCents, string $currency, array $metadata): array
    {
        $intent = $this->client()->paymentIntents->create([
            'amount' => $amountInCents,
            'currency' => strtolower($currency),
            'payment_method_types' => ['card'],
            'metadata' => $metadata,
        ]);

        return [
            'id' => $intent->id,
            'client_secret' => $intent->client_secret,
        ];
    }

    public function retrieveCheckout(string $checkoutId): object
    {
        return $this->client()->checkout->sessions->retrieve($checkoutId, [
            'expand' => ['payment_intent.latest_charge'],
        ]);
    }

    public function retrievePaymentIntent(string $paymentIntentId): object
    {
        return $this->client()->paymentIntents->retrieve($paymentIntentId, [
            'expand' => ['latest_charge'],
        ]);
    }

    public function constructWebhookEvent(string $payload, ?string $signature): object
    {
        $settings = $this->settings();

        if (! $settings?->stripe_webhook_secret) {
            throw new SignatureVerificationException('Stripe webhook secret is not configured.', null);
        }

        return Webhook::constructEvent($payload, $signature ?? '', $settings->stripe_webhook_secret);
    }

    public function refund(PurchaseTransaction $payment, int $amountInCents): object
    {
        $payload = [
            'payment_intent' => $payment->provider_transaction_id ?: $payment->stripe_payment_intent_id,
        ];

        $fullAmountInCents = (int) round(((float) $payment->amount) * 100);

        if ($amountInCents < $fullAmountInCents) {
            $payload['amount'] = $amountInCents;
        }

        return $this->client()->refunds->create($payload);
    }

    private function settings(): ?SiteSetting
    {
        return SiteSetting::query()->first();
    }

    private function client(): StripeClient
    {
        return new StripeClient((string) $this->settings()?->stripe_secret_key);
    }
}
