<?php

namespace App\Http\Controllers\Subscription;

use App\Actions\Subscription\CreatePaymentIntent;
use App\Actions\Subscription\CreatePurchaseComplaint;
use App\Actions\Subscription\GetCreditHistory;
use App\Actions\Subscription\GetPurchaseCreditPageData;
use App\Actions\Subscription\GetPurchaseHistory;
use App\Actions\Subscription\HandleCheckoutSuccess;
use App\Actions\Subscription\HandlePaymentIntentSuccess;
use App\Actions\Subscription\ProcessCreditCheckout;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutPurchaseCreditRequest;
use App\Models\PurchaseTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseCreditController extends Controller
{
    public function __construct(
        private GetPurchaseCreditPageData $getPurchaseCreditPageData,
        private ProcessCreditCheckout $processCreditCheckout,
        private HandleCheckoutSuccess $handleCheckoutSuccess,
        private HandlePaymentIntentSuccess $handlePaymentIntentSuccess,
        private GetPurchaseHistory $getPurchaseHistory,
        private CreatePurchaseComplaint $createPurchaseComplaint,
        private GetCreditHistory $getCreditHistory,
        private CreatePaymentIntent $createPaymentIntent,
    ) {}

    public function purchaseCredit(): View
    {
        $data = $this->getPurchaseCreditPageData->execute();

        if ($data['lockedPackageId'] ?? null) {
            request()->session()->put('purchase_credit_locked_package_id', $data['lockedPackageId']);
        } else {
            request()->session()->forget('purchase_credit_locked_package_id');
        }

        return view('subscription.purchase-credit', $data);
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
        // Handle embedded PaymentElement flow
        if ($request->get('payment_intent')) {
            $paymentIntentId = $request->get('payment_intent');
            $result = $this->handlePaymentIntentSuccess->execute($paymentIntentId);

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

        // Handle Stripe hosted checkout flow
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

    public function createPaymentIntent(CheckoutPurchaseCreditRequest $request): JsonResponse
    {
        $result = $this->createPaymentIntent->execute($request->validated());

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 422);
        }

        return response()->json($result);
    }

    public function creditHistory(): View
    {
        return view('subscription.credit-history', $this->getCreditHistory->execute());
    }

    public function creditHistoryLastMonth(): RedirectResponse
    {
        $lastMonth = now()->subMonth()->format('Y-m');

        return redirect()->route('credit-history', ['month' => $lastMonth]);
    }

    public function purchaseHistory(): View
    {
        return view('subscription.purchase-history', $this->getPurchaseHistory->execute());
    }

    public function storeComplaint(Request $request, PurchaseTransaction $purchaseTransaction): RedirectResponse
    {
        $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        if ($purchaseTransaction->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($purchaseTransaction->complaints()->whereIn('status', ['pending', 'reviewed'])->exists()) {
            return redirect()->route('purchase-history')
                ->withErrors('A complaint for this transaction is already under review.');
        }

        $this->createPurchaseComplaint->execute($purchaseTransaction, $request->only('subject', 'message'));

        return redirect()->route('purchase-history')
            ->with('complaint_success', 'Your complaint has been submitted. We will review it and respond shortly.');
    }
}
