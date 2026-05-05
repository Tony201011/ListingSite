<?php

namespace App\Actions\Subscription;

use App\Models\PricingPackage;
use App\Models\PurchaseTransaction;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Auth;
use Stripe\StripeClient;

class ProcessCreditCheckout
{
    /**
     * @return array{credits: int, price: float, invoice_name: string, checkout_url?: string}
     */
    public function execute(array $validated): array
    {
        $selectedCredits = (int) $validated['credits'];

        $package = PricingPackage::query()
            ->where('credits', $selectedCredits)
            ->where('is_active', true)
            ->first();

        $selectedPrice = $package ? (float) preg_replace('/[^\d.]/', '', $package->total_price) : 0;

        $result = [
            'credits' => $selectedCredits,
            'price' => $selectedPrice,
            'invoice_name' => $validated['invoice_name'],
        ];

        // Check if Stripe is enabled
        $siteSetting = SiteSetting::first();
        if (!$siteSetting?->stripe_enabled || !$siteSetting->stripe_secret_key) {
            return $result;
        }

        // Create transaction record
        $transaction = PurchaseTransaction::create([
            'user_id' => Auth::id(),
            'credits' => $selectedCredits,
            'amount' => $selectedPrice,
            'currency' => 'AUD',
            'status' => 'pending',
            'invoice_name' => $validated['invoice_name'],
        ]);

        // Create Stripe checkout session
        $stripe = new StripeClient($siteSetting->stripe_secret_key);

        $checkoutSession = $stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'aud',
                    'product_data' => [
                        'name' => "{$selectedCredits} Credits Package",
                        'description' => "Purchase {$selectedCredits} credits for AUD $" . number_format($selectedPrice, 2),
                    ],
                    'unit_amount' => (int) ($selectedPrice * 100), // Amount in cents
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('purchase-credit.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('purchase-credit'),
            'metadata' => [
                'user_id' => Auth::id(),
                'credits' => $selectedCredits,
                'transaction_id' => $transaction->id,
                'invoice_name' => $validated['invoice_name'],
            ],
            'customer_email' => Auth::user()->email,
        ]);

        // Update transaction with Stripe session ID
        $transaction->update(['stripe_session_id' => $checkoutSession->id]);

        $result['checkout_url'] = $checkoutSession->url;

        return $result;
    }
}
