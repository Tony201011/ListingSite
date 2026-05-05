<?php

namespace App\Http\Controllers\Subscription;

use App\Actions\Subscription\ProcessCreditCheckout;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutPurchaseCreditRequest;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Stripe\StripeClient;

class PurchaseCreditController extends Controller
{
    public function __construct(
        private ProcessCreditCheckout $processCreditCheckout
    ) {}

    public function purchaseCredit(): View
    {
        return view('subscription.purchase-credit');
    }

    public function checkout(CheckoutPurchaseCreditRequest $request): RedirectResponse
    {
        $result = $this->processCreditCheckout->execute($request->validated());

        if (isset($result['checkout_url'])) {
            return redirect($result['checkout_url']);
        }

        return redirect('/purchase-history')->with(
            'checkout_success',
            "Checkout started for {$result['credits']} credits (AUD $".
            number_format($result['price'], 2).
            ") under invoice name '{$result['invoice_name']}'."
        );
    }

    public function checkoutSuccess(Request $request): RedirectResponse
    {
        $sessionId = $request->get('session_id');

        if (!$sessionId) {
            return redirect('/purchase-credit')->withErrors('Invalid checkout session.');
        }

        $siteSetting = SiteSetting::first();
        if (!$siteSetting?->stripe_enabled || !$siteSetting->stripe_secret_key) {
            return redirect('/purchase-credit')->withErrors('Payment system is not configured.');
        }

        try {
            $stripe = new StripeClient($siteSetting->stripe_secret_key);
            $session = $stripe->checkout->sessions->retrieve($sessionId);

            if ($session->payment_status === 'paid') {
                // Process the successful payment
                $userId = $session->metadata->user_id;
                $credits = $session->metadata->credits;

                // Add credits to user account
                $user = User::find($userId);
                if ($user) {
                    $user->increment('credits', $credits);

                    // Log the purchase
                    // You might want to create a purchase history record here
                }

                return redirect('/purchase-history')->with(
                    'checkout_success',
                    "Payment successful! {$credits} credits have been added to your account."
                );
            }

            return redirect('/purchase-credit')->withErrors('Payment was not completed.');
        } catch (\Exception $e) {
            return redirect('/purchase-credit')->withErrors('Failed to verify payment: ' . $e->getMessage());
        }
    }

    public function creditHistory(): View
    {
        return view('subscription.credit-history');
    }

    public function creditHistoryLastMonth(): View
    {
        return view('subscription.credit-history-last-month');
    }

    public function purchaseHistory(): View
    {
        return view('subscription.purchase-history');
    }
}
