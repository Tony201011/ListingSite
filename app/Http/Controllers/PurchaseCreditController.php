<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PurchaseCreditController extends Controller
{

    public function purchaseCredit(Request $request)
    {
        return view('purchase-credit');
    }

    public function checkout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'credits' => ['required', 'integer', Rule::in([7, 30, 60, 120, 180])],
            'invoice_name' => ['required', 'string', 'max:120'],
        ]);

        $prices = [
            7 => 10,
            30 => 35,
            60 => 65,
            120 => 120,
            180 => 160,
        ];

        $selectedCredits = (int) $validated['credits'];
        $selectedPrice = $prices[$selectedCredits] ?? 0;

        return redirect('/purchase-history')->with('checkout_success', "Checkout started for {$selectedCredits} credits (AUD $" . number_format($selectedPrice, 2) . ") under invoice name '{$validated['invoice_name']}'.");
    }

    public function creditHistory(Request $request)
    {
        return view('credit-history');
    }

    public function creditHistoryLastMonth(Request $request)
    {
        return view('credit-history-last-month');
    }

    public function purchaseHistory(Request $request)
    {
        return view('purchase-history');
    }
}
