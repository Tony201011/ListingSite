<?php

namespace App\Http\Controllers\Subscription;

use App\Actions\Subscription\GetPurchaseCreditPageData;
use App\Actions\Subscription\GetPurchaseHistory;
use App\Actions\Subscription\HandleCheckoutSuccess;
use App\Actions\Subscription\ProcessCreditCheckout;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutPurchaseCreditRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseCreditController extends Controller
{
    public function __construct(
        private GetPurchaseCreditPageData $getPurchaseCreditPageData,
        private ProcessCreditCheckout $processCreditCheckout,
        private HandleCheckoutSuccess $handleCheckoutSuccess,
        private GetPurchaseHistory $getPurchaseHistory,
    ) {}

    public function purchaseCredit(): View
    {
        return view('subscription.purchase-credit', $this->getPurchaseCreditPageData->execute());
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

        if (! $sessionId) {
            return redirect('/purchase-credit')->withErrors('Invalid checkout session.');
        }

        $result = $this->handleCheckoutSuccess->execute($sessionId);

        return match ($result['status']) {
            'paid' => redirect('/purchase-history')->with(
                'checkout_success',
                "Payment successful! {$result['credits']} credits have been added to your account."
            ),
            'not_found' => redirect('/purchase-credit')->withErrors('Transaction not found.'),
            'not_configured' => redirect('/purchase-credit')->withErrors('Payment system is not configured.'),
            'unpaid' => redirect('/purchase-credit')->withErrors('Payment was not completed.'),
            'error' => redirect('/purchase-credit')->withErrors('Failed to verify payment: '.$result['error']),
            default => redirect('/purchase-credit')->withErrors('An unexpected error occurred.'),
        };
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
        return view('subscription.purchase-history', $this->getPurchaseHistory->execute());
    }
}
