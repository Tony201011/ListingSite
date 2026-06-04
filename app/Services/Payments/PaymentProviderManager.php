<?php

namespace App\Services\Payments;

use App\Models\SiteSetting;
use InvalidArgumentException;

class PaymentProviderManager
{
    public function __construct(
        private StripePaymentProvider $stripePaymentProvider,
    ) {}

    public function current(): PaymentProviderInterface
    {
        $provider = SiteSetting::query()->value('default_payment_provider') ?: 'stripe';

        return $this->for($provider);
    }

    public function for(string $provider): PaymentProviderInterface
    {
        return match ($provider) {
            'stripe' => $this->stripePaymentProvider,
            default => throw new InvalidArgumentException("Unsupported payment provider [{$provider}]."),
        };
    }
}
