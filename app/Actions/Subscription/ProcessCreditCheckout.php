<?php

namespace App\Actions\Subscription;

use App\Models\CreditPackage;
use App\Models\PurchaseTransaction;
use App\Services\Payments\PaymentProviderManager;
use Illuminate\Support\Facades\Auth;

class ProcessCreditCheckout
{
    public function __construct(
        private PaymentProviderManager $paymentProviderManager,
    ) {}

    /**
     * @return array{credits: int, price: float, invoice_name: string, checkout_url?: string, error?: string}
     */
    public function execute(array $validated): array
    {
        $package = CreditPackage::query()
            ->active()
            ->findOrFail($validated['package_id']);

        $selectedCredits = $package->total_credits;
        $selectedPrice = (float) $package->price;
        $provider = $this->paymentProviderManager->current();

        $result = [
            'credits' => $selectedCredits,
            'price' => $selectedPrice,
            'invoice_name' => $validated['invoice_name'],
        ];

        if (! $provider->isConfigured()) {
            $result['error'] = 'Payment processing is unavailable until processor approval is completed.';

            return $result;
        }

        $transaction = PurchaseTransaction::create([
            'user_id' => Auth::id(),
            'provider_profile_id' => (int) $validated['provider_profile_id'],
            'provider' => $provider->name(),
            'credit_package_id' => $package->id,
            'credits' => (int) $package->credits,
            'bonus_credits' => (int) $package->bonus_credits,
            'amount' => $selectedPrice,
            'currency' => $package->currency ?: 'AUD',
            'status' => 'pending',
            'invoice_name' => $validated['invoice_name'],
            'metadata' => [
                'package_name' => $package->name,
                'base_credits' => (int) $package->credits,
                'bonus_credits' => (int) $package->bonus_credits,
            ],
        ]);

        $checkoutSession = $provider->createCheckout($transaction, [
            'price_data' => [
                'currency' => strtolower($package->currency ?: 'AUD'),
                'product_data' => [
                    'name' => $package->name,
                    'description' => $package->description ?: "Purchase {$selectedCredits} credits for ".($package->currency ?: 'AUD').' $'.number_format($selectedPrice, 2),
                ],
                'unit_amount' => (int) round($selectedPrice * 100),
            ],
            'quantity' => 1,
        ], [
            'user_id' => Auth::id(),
            'credits' => (int) $package->credits,
            'bonus_credits' => (int) $package->bonus_credits,
            'package_id' => $package->id,
            'provider_profile_id' => (int) $validated['provider_profile_id'],
            'transaction_id' => $transaction->id,
            'invoice_name' => $validated['invoice_name'],
        ], (string) Auth::user()->email);

        $transaction->update([
            'provider_checkout_id' => $checkoutSession['id'],
            'stripe_session_id' => $provider->name() === 'stripe' ? $checkoutSession['id'] : $transaction->stripe_session_id,
        ]);

        $result['checkout_url'] = $checkoutSession['url'];

        return $result;
    }
}
