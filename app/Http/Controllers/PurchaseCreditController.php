<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutPurchaseCreditRequest;
use Illuminate\Http\RedirectResponse;

class PurchaseCreditController extends Controller
{
    public function purchaseCredit()
    {
        return view('subscription.purchase-credit');
    }

    public function checkout(CheckoutPurchaseCreditRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $prices = [
            7 => 10,
            30 => 35,
            60 => 65,
            120 => 120,
            180 => 160,
        ];

        $selectedCredits = (int) $validated['credits'];
        $selectedPrice = $prices[$selectedCredits] ?? 0;

        return redirect('/purchase-history')->with(
            'checkout_success',
            "Checkout started for {$selectedCredits} credits (AUD $" .
            number_format($selectedPrice, 2) .
            ") under invoice name '{$validated['invoice_name']}'."
        );
    }

    public function creditHistory()
    {
        return view('subscription.credit-history');
    }

    public function creditHistoryLastMonth()
    {
        return view('subscription.credit-history-last-month');
    }

    public function purchaseHistory()
    {
        return view('subscription.purchase-history');
    }
}
