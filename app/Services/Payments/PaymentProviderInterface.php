<?php

namespace App\Services\Payments;

use App\Models\PurchaseTransaction;

interface PaymentProviderInterface
{
    public function name(): string;

    public function isConfigured(): bool;

    public function publicKey(): ?string;

    /**
     * @param  array<string, mixed>  $lineItem
     * @param  array<string, mixed>  $metadata
     * @return array{id: string, url: string}
     */
    public function createCheckout(PurchaseTransaction $payment, array $lineItem, array $metadata, string $customerEmail): array;

    /**
     * @param  array<string, mixed>  $metadata
     * @return array{id: string, client_secret: string}
     */
    public function createPaymentIntent(PurchaseTransaction $payment, int $amountInCents, string $currency, array $metadata): array;

    public function retrieveCheckout(string $checkoutId): object;

    public function retrievePaymentIntent(string $paymentIntentId): object;

    public function constructWebhookEvent(string $payload, ?string $signature): object;

    public function refund(PurchaseTransaction $payment, int $amountInCents): object;
}
