<?php

namespace App\Actions\Subscription;

use App\Models\CreditPackage;
use App\Models\PurchaseTransaction;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Auth;
use Stripe\StripeClient;

class CreatePaymentIntent
{
    /**
     * @return array{client_secret: string, transaction_id: int, credits: int, price: float}|array{error: string}
     */
    public function execute(array $validated): array
    {
        $package = CreditPackage::where('id', $validated['package_id'])
            ->where('status', 'active')
            ->firstOrFail();

        $siteSetting = SiteSetting::first();

        if (! $siteSetting?->stripe_enabled || ! $siteSetting->stripe_secret_key) {
            return ['error' => 'Payment system is not configured.'];
        }

        $transaction = PurchaseTransaction::create([
            'user_id' => Auth::id(),
            'credits' => $package->credits,
            'amount' => $package->price,
            'currency' => 'AUD',
            'status' => 'pending',
            'invoice_name' => $validated['invoice_name'],
        ]);

        $stripe = new StripeClient($siteSetting->stripe_secret_key);

        $paymentIntent = $stripe->paymentIntents->create([
            'amount' => (int) ($package->price * 100),
            'currency' => 'aud',
            'automatic_payment_methods' => ['enabled' => true],
            'metadata' => [
                'user_id' => Auth::id(),
                'credits' => $package->credits,
                'package_id' => $package->id,
                'transaction_id' => $transaction->id,
                'invoice_name' => $validated['invoice_name'],
            ],
        ]);

        $transaction->update(['stripe_payment_intent_id' => $paymentIntent->id]);

        return [
            'client_secret' => $paymentIntent->client_secret,
            'transaction_id' => $transaction->id,
            'credits' => $package->credits,
            'price' => (float) $package->price,
        ];
    }
}
