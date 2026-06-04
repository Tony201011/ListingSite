<?php

namespace App\Actions\Subscription;

use App\Models\CreditPackage;
use App\Models\PurchaseTransaction;
use App\Services\Payments\PaymentProviderManager;
use Illuminate\Support\Facades\Auth;

class CreatePaymentIntent
{
    public function __construct(
        private PaymentProviderManager $paymentProviderManager,
    ) {}

    /**
     * @return array{client_secret: string, transaction_id: int, credits: int, price: float}|array{error: string}
     */
    public function execute(array $validated): array
    {
        $package = CreditPackage::query()
            ->active()
            ->findOrFail($validated['package_id']);
        $provider = $this->paymentProviderManager->current();

        if (! $provider->isConfigured()) {
            return ['error' => 'Payment system is not configured.'];
        }

        $transaction = PurchaseTransaction::create([
            'user_id' => Auth::id(),
            'provider_profile_id' => (int) $validated['provider_profile_id'],
            'provider' => $provider->name(),
            'credit_package_id' => $package->id,
            'credits' => $package->credits,
            'bonus_credits' => $package->bonus_credits,
            'amount' => $package->price,
            'currency' => $package->currency ?: 'AUD',
            'status' => 'pending',
            'invoice_name' => $validated['invoice_name'],
            'metadata' => [
                'package_name' => $package->name,
                'base_credits' => (int) $package->credits,
                'bonus_credits' => (int) $package->bonus_credits,
            ],
        ]);

        $paymentIntent = $provider->createPaymentIntent(
            $transaction,
            (int) round(((float) $package->price) * 100),
            $package->currency ?: 'AUD',
            [
                'user_id' => Auth::id(),
                'credits' => (int) $package->credits,
                'bonus_credits' => (int) $package->bonus_credits,
                'package_id' => $package->id,
                'provider_profile_id' => (int) $validated['provider_profile_id'],
                'transaction_id' => $transaction->id,
                'invoice_name' => $validated['invoice_name'],
            ],
        );

        $transaction->update([
            'provider_transaction_id' => $paymentIntent['id'],
            'stripe_payment_intent_id' => $provider->name() === 'stripe' ? $paymentIntent['id'] : $transaction->stripe_payment_intent_id,
        ]);

        return [
            'client_secret' => $paymentIntent['client_secret'],
            'transaction_id' => $transaction->id,
            'credits' => $package->total_credits,
            'price' => (float) $package->price,
        ];
    }
}
