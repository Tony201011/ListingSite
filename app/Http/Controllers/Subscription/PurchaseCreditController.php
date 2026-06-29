<?php

namespace App\Http\Controllers\Subscription;

use App\Actions\GetActiveProviderProfile;
use App\Actions\Subscription\CreatePaymentIntent;
use App\Actions\Subscription\CreatePurchaseComplaint;
use App\Actions\Subscription\GetCreditHistory;
use App\Actions\Subscription\GetPurchaseCreditPageData;
use App\Actions\Subscription\GetPurchaseHistory;
use App\Actions\Subscription\HandleCheckoutSuccess;
use App\Actions\Subscription\HandlePaymentIntentSuccess;
use App\Actions\Subscription\InitiateWooCommerceCheckout;
use App\Actions\Subscription\ProcessCreditCheckout;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutPurchaseCreditRequest;
use App\Models\CreditPurchase;
use App\Models\CreditPackage;
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
        private GetActiveProviderProfile $getActiveProviderProfile,
        private InitiateWooCommerceCheckout $initiateWooCommerceCheckout,
    ) {}

    public function purchaseCredit(Request $request): View|RedirectResponse
    {
        $data = $this->getPurchaseCreditPageData->execute();

        if (
            $request->boolean('lock_package')
            && $request->user()
            && ($data['woocommerceEnabled'] ?? false)
            && ($data['selectedPackage'] ?? null)?->hasWooProduct()
            && ($data['activeProfile'] ?? null)
        ) {
            $result = $this->initiateWooCommerceCheckout->execute(
                $request->user(),
                $data['selectedPackage'],
                $data['activeProfile'],
            );

            if (isset($result['checkout_url'])) {
                return redirect()->away($result['checkout_url']);
            }
        }

        if ($data['lockedPackageId'] ?? null) {
            request()->session()->put('purchase_credit_locked_package_id', $data['lockedPackageId']);
        } else {
            request()->session()->forget('purchase_credit_locked_package_id');
        }

        return view('subscription.purchase-credit', $data);
    }

    public function checkout(CheckoutPurchaseCreditRequest $request): RedirectResponse
    {
        $setting = \App\Models\SiteSetting::query()->first();
        if ($setting && ! $setting->checkout_enabled) {
            return redirect('/purchase-credit')->withErrors('Checkout is currently disabled. Please try again later.');
        }

        $validated = $request->validated();
        $result = $this->processCreditCheckout->execute($validated);
        $activeProfile = $this->getActiveProviderProfile->execute($request->user());
        $profileName = $activeProfile?->name ?? 'selected profile';

        if (isset($result['error'])) {
            return redirect('/purchase-credit')->withErrors($result['error']);
        }

        if (isset($result['checkout_url'])) {
            return redirect($result['checkout_url']);
        }

        return redirect('/purchase-history')->with(
            'checkout_success',
            "Checkout started for {$result['credits']} credits (AUD $".
            number_format($result['price'], 2).
            ") for {$profileName} under invoice name '{$result['invoice_name']}'."
        );
    }

    public function wooCheckout(CheckoutPurchaseCreditRequest $request): RedirectResponse
    {
        $setting = \App\Models\SiteSetting::query()->first();
        if ($setting && ! $setting->checkout_enabled) {
            return redirect('/purchase-credit')->withErrors('Checkout is currently disabled. Please try again later.');
        }

        $validated = $request->validated();

        $package = CreditPackage::query()->active()->findOrFail($validated['package_id']);
        $profile = $this->getActiveProviderProfile->execute($request->user());

        if (! $profile) {
            return redirect('/purchase-credit')->withErrors('No active provider profile found.');
        }

        $result = $this->initiateWooCommerceCheckout->execute($request->user(), $package, $profile);

        if (isset($result['error'])) {
            return redirect('/purchase-credit')->withErrors($result['error']);
        }

        return redirect($result['checkout_url']);
    }

    public function checkoutSuccess(Request $request): RedirectResponse
    {
        if ($request->string('provider')->toString() === 'woocommerce' || $request->filled('purchase_uuid')) {
            return $this->handleWooCommerceSuccess($request);
        }

        // Handle embedded PaymentElement flow
        if ($request->get('payment_intent')) {
            $paymentIntentId = $request->get('payment_intent');
            $result = $this->handlePaymentIntentSuccess->execute($paymentIntentId);
            $profile = $this->getActiveProviderProfile->execute($request->user());
            $profileName = $profile?->name ?? 'selected profile';

            return match ($result['status']) {
                'paid' => redirect('/purchase-history')->with(
                    'checkout_success',
                    "Payment successful! {$result['credits']} credits have been added to {$profileName}."
                ),
                'not_found' => redirect('/purchase-credit')->withErrors('Transaction not found.'),
                'not_configured' => redirect('/purchase-credit')->withErrors('Payment system is not configured.'),
                'unpaid' => redirect('/purchase-credit')->withErrors('Payment was not completed.'),
                'error' => redirect('/purchase-credit')->withErrors('Failed to verify payment: '.$result['error']),
                default => redirect('/purchase-credit')->withErrors('An unexpected error occurred.'),
            };
        }

        private function handleWooCommerceSuccess(Request $request): RedirectResponse
        {
            $purchaseUuid = (string) $request->get('purchase_uuid', '');

            if ($purchaseUuid === '') {
                return redirect('/purchase-credit')->withErrors('Invalid WooCommerce purchase.');
            }

            $purchase = CreditPurchase::query()
                ->with('providerProfile')
                ->where('uuid', $purchaseUuid)
                ->first();

            if (! $purchase) {
                return redirect('/purchase-credit')->withErrors('WooCommerce purchase not found.');
            }

            if ($request->user() && $purchase->user_id !== $request->user()->id) {
                return redirect('/purchase-credit')->withErrors('WooCommerce purchase not found.');
            }

            $profileName = $purchase->providerProfile?->name ?? 'selected profile';

            return match ($purchase->status) {
                'paid' => redirect('/purchase-history')->with(
                    'checkout_success',
                    "Payment successful! {$purchase->credits} credits have been added to {$profileName}."
                ),
                'pending' => redirect('/purchase-credit')->with(
                    'checkout_success',
                    'Your WooCommerce payment is being processed. Credits will appear shortly.'
                ),
                'cancelled', 'refunded' => redirect('/purchase-credit')->withErrors('This WooCommerce order was not completed.'),
                default => redirect('/purchase-credit')->with(
                    'checkout_success',
                    'Your WooCommerce payment has been received and is being processed.'
                ),
            };
        }

        // Handle Stripe hosted checkout flow
        $sessionId = $request->get('session_id');

        if (! $sessionId) {
            return redirect('/purchase-credit')->withErrors('Invalid checkout session.');
        }

        $result = $this->handleCheckoutSuccess->execute($sessionId);
        $profile = $this->getActiveProviderProfile->execute($request->user());
        $profileName = $profile?->name ?? 'selected profile';

        return match ($result['status']) {
            'paid' => redirect('/purchase-history')->with(
                'checkout_success',
                "Payment successful! {$result['credits']} credits have been added to {$profileName}."
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

        $activeProfile = $this->getActiveProviderProfile->execute($request->user());
        if (! $activeProfile || $purchaseTransaction->provider_profile_id !== $activeProfile->id) {
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
