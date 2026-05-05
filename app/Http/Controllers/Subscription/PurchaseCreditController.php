<?php

namespace App\Http\Controllers\Subscription;

use App\Actions\Subscription\ProcessCreditCheckout;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutPurchaseCreditRequest;
use App\Models\PricingPackage;
use App\Models\PurchaseTransaction;
use App\Models\SiteSetting;
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
        $currentBalance = auth()->user()->credits ?? 0;

        $packages = PricingPackage::where('is_active', true)->orderBy('credits')->get();

        if ($packages->isNotEmpty()) {
            $plans = $packages->map(fn ($pkg) => [
                'credits' => (int) $pkg->credits,
                'price' => (float) preg_replace('/[^\d.]/', '', $pkg->total_price),
            ])->toArray();
        } else {
            $plans = [
                ['credits' => 7, 'price' => 10],
                ['credits' => 30, 'price' => 35],
                ['credits' => 60, 'price' => 65],
                ['credits' => 120, 'price' => 120],
                ['credits' => 180, 'price' => 160],
            ];
        }

        return view('subscription.purchase-credit', compact('currentBalance', 'plans'));
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

        $siteSetting = SiteSetting::first();
        if (! $siteSetting?->stripe_enabled || ! $siteSetting->stripe_secret_key) {
            return redirect('/purchase-credit')->withErrors('Payment system is not configured.');
        }

        try {
            $stripe = new StripeClient($siteSetting->stripe_secret_key);
            $session = $stripe->checkout->sessions->retrieve($sessionId);

            if ($session->payment_status === 'paid') {
                $transaction = PurchaseTransaction::where('stripe_session_id', $sessionId)->first();

                if ($transaction && $transaction->status !== 'paid') {
                    $transaction->update([
                        'status' => 'paid',
                        'stripe_payment_intent_id' => $session->payment_intent,
                        'paid_at' => now(),
                    ]);

                    $user = $transaction->user;
                    if ($user) {
                        $user->increment('credits', $transaction->credits);
                    }

                    $credits = $transaction->credits;
                } elseif ($transaction) {
                    // Already processed (e.g., by webhook)
                    $credits = $transaction->credits;
                } else {
                    $credits = (int) ($session->metadata->credits ?? 0);
                }

                return redirect('/purchase-history')->with(
                    'checkout_success',
                    "Payment successful! {$credits} credits have been added to your account."
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
            $query->whereYear('created_at', substr($month, 0, 4))
                ->whereMonth('created_at', substr($month, 5, 2));
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

        // Get available months for filter
        $availableMonths = PurchaseTransaction::where('user_id', auth()->id())
            ->selectRaw('DISTINCT YEAR(created_at) as year, MONTH(created_at) as month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'value' => sprintf('%04d-%02d', $item->year, $item->month),
                    'label' => date('M Y', strtotime("{$item->year}-{$item->month}-01")),
                ];
            });

        return view('subscription.purchase-history', compact('purchases', 'availableMonths'));
    }
}
