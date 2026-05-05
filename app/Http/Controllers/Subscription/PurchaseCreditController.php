<?php

namespace App\Http\Controllers\Subscription;

use App\Actions\Subscription\ProcessCreditCheckout;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutPurchaseCreditRequest;
use App\Models\CreditPackage;
use App\Models\PurchaseTransaction;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Stripe\StripeClient;

class PurchaseCreditController extends Controller
{
    public function __construct(
        private ProcessCreditCheckout $processCreditCheckout
    ) {}

    public function purchaseCredit(): View
    {
        $user = auth()->user();

        $packages = CreditPackage::where('status', 'active')
            ->orderBy('sort_order', 'asc')
            ->orderBy('price', 'asc')
            ->get();

        $defaultPackageId = $packages->first()?->id;
        $selectedPackageId = (int) old('package_id', request('package_id', $defaultPackageId));

        if (! $packages->contains('id', $selectedPackageId)) {
            $selectedPackageId = $defaultPackageId;
        }

        return view('subscription.purchase-credit', [
            'currentBalance' => $user->credits ?? 0,
            'userName' => $user->name ?? 'User',
            'packages' => $packages,
            'selectedPackageId' => $selectedPackageId,
        ]);
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

        // Look up the transaction that the webhook should have already processed
        $transaction = PurchaseTransaction::where('stripe_session_id', $sessionId)
            ->where('user_id', auth()->id())
            ->first();

        if (! $transaction) {
            return redirect('/purchase-credit')->withErrors('Transaction not found.');
        }

        if ($transaction->status === 'paid') {
            return redirect('/purchase-history')->with(
                'checkout_success',
                "Payment successful! {$transaction->credits} credits have been added to your account."
            );
        }

        // Webhook may not have arrived yet — verify directly with Stripe as fallback
        $siteSetting = SiteSetting::first();
        if (! $siteSetting?->stripe_enabled || ! $siteSetting->stripe_secret_key) {
            return redirect('/purchase-credit')->withErrors('Payment system is not configured.');
        }

        try {
            $stripe = new StripeClient($siteSetting->stripe_secret_key);
            $session = $stripe->checkout->sessions->retrieve($sessionId);

            if ($session->payment_status === 'paid') {
                // Webhook has not processed yet — mark transaction and add credits now,
                // using a DB-level guard so a subsequent webhook delivery does not double-credit.
                if ($transaction->status !== 'paid') {
                    DB::transaction(function () use ($transaction, $session) {
                        // Re-read inside transaction with row lock to prevent race with webhook
                        $locked = PurchaseTransaction::lockForUpdate()->find($transaction->id);
                        if ($locked && $locked->status !== 'paid') {
                            $locked->update([
                                'status' => 'paid',
                                'stripe_payment_intent_id' => $session->payment_intent,
                                'paid_at' => now(),
                            ]);
                            $locked->user?->increment('credits', $locked->credits);
                        }
                    });

                    $transaction->refresh();
                }

                return redirect('/purchase-history')->with(
                    'checkout_success',
                    "Payment successful! {$transaction->credits} credits have been added to your account."
                );
            }

            return redirect('/purchase-credit')->withErrors('Payment was not completed.');
        } catch (\Exception $e) {
            return redirect('/purchase-credit')->withErrors('Failed to verify payment: '.$e->getMessage());
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
        $query = PurchaseTransaction::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc');

        // Apply filters
        $status = request('status', 'all');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $month = request('month', 'all');
        if ($month !== 'all') {
            $query->whereRaw('SUBSTR(created_at, 1, 7) = ?', [$month]);
        }

        $search = trim(request('q', ''));
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_name', 'like', "%{$search}%")
                    ->orWhere('credits', 'like', "%{$search}%")
                    ->orWhere('amount', 'like', "%{$search}%");
            });
        }

        $purchases = $query->paginate(20);

        // Get available months for filter — use SUBSTR for cross-DB compatibility (MySQL + SQLite)
        $availableMonths = PurchaseTransaction::where('user_id', auth()->id())
            ->selectRaw('DISTINCT SUBSTR(created_at, 1, 7) as month_key')
            ->orderByRaw('month_key DESC')
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->month_key,
                    'label' => date('M Y', strtotime($item->month_key.'-01')),
                ];
            });

        return view('subscription.purchase-history', compact('purchases', 'availableMonths'));
    }
}
