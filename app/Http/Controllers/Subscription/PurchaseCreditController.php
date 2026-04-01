<?php

namespace App\Http\Controllers\Subscription;

use App\Actions\Subscription\ProcessCreditCheckout;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutPurchaseCreditRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

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

        return redirect('/purchase-history')->with(
            'checkout_success',
            "Checkout started for {$result['credits']} credits (AUD $".
            number_format($result['price'], 2).
            ") under invoice name '{$result['invoice_name']}'."
        );
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
