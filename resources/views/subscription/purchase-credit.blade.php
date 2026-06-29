@extends('layouts.frontend')

@section('content')
@php
    $packagesJson = $packages->map(fn ($p) => [
        'id' => $p->id,
        'name' => $p->name,
        'credits' => $p->total_credits,
        'bonus_credits' => $p->bonus_credits,
        'price' => number_format($p->price, 2),
        'currency' => $p->currency,
    ])->values()->toJson();
@endphp
<div id="purchase-credit-flow" class="min-h-screen bg-gray-50 px-4 py-10 sm:px-6 lg:px-8" x-data="{
    selectedPackageId: {{ $selectedPackageId ?? 'null' }},
    lockedPackageId: {{ $lockedPackageId ?? 'null' }},
    packages: {{ $packagesJson }},
    step: 'select',
    processing: false,
    paymentError: null,
    stripeReady: false,
    termsAccepted: false,
    get selected() {
        return this.packages.find(p => p.id === this.selectedPackageId) ?? null;
    }
}">
    <div class="mx-auto w-full max-w-5xl">
        <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">Add Wallet Credits</h1>
                <p class="mt-3 text-gray-600">One credit for every day your profile is online.</p>
            </div>
            @if(!($guestMode ?? false))
                <a href="{{ route('my-profile') }}" class="text-xs sm:text-sm font-medium text-[#e04ecb] transition hover:text-[#c13ab0] hover:underline">&larr; Back to dashboard</a>
            @endif
        </div>

        <div class="mb-5">
            @include('subscription.partials.pricing-benefits', ['pricingPage' => $pricingPage ?? null])
        </div>

        @if(!($guestMode ?? false))
        <div class="mb-5 rounded-2xl border border-pink-100 bg-pink-50 p-4 text-xs sm:text-sm text-gray-700 shadow-sm">
            <div class="font-semibold text-gray-900">Selected profile: {{ $activeProfile?->name ?? 'Not selected' }}</div>
            Your current credits balance is <span class="font-semibold text-gray-900">{{ $currentBalance }}</span>.
            You are charged <span class="font-semibold text-gray-900">1 credit per day</span> while your profile is visible.
        </div>
        @endif

        @if(session('checkout_success'))
            <div class="mb-5 rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-sm text-emerald-700 shadow-sm">
                {{ session('checkout_success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-5 rounded-2xl border border-rose-100 bg-rose-50 p-4 text-sm text-rose-700 shadow-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Advertising credits purpose notice --}}
        <div class="mb-5 rounded-2xl border border-blue-100 bg-blue-50 p-4 text-sm text-blue-800 shadow-sm">
            You are purchasing advertising credits for use on hotescort.com.au. Credits are used for profile visibility and promotional listing features only. The platform does not process bookings, deposits, appointment payments, escort payments, or payments between visitors and advertisers.
        </div>

        @if(!($checkoutEnabled ?? true) || !($paymentEnabled ?? false) || ($stripeTestMode ?? false))
            <div class="mb-5 rounded-2xl border border-amber-100 bg-amber-50 p-4 text-sm text-amber-800 shadow-sm">
                @if(!($checkoutEnabled ?? true))
                    <p class="font-semibold">Checkout Disabled</p>
                    <p class="mt-1">Checkout is currently disabled by admin. Please try again later.</p>
                @elseif(!($paymentEnabled ?? false))
                    <p class="font-semibold">Payment Unavailable</p>
                    <p class="mt-1">Payment processing is currently unavailable. Please contact support.</p>
                @else
                    <p class="font-semibold">Test Mode</p>
                    <p class="mt-1">Test checkout mode is enabled. Use test card details to complete checkout safely.</p>
                @endif
            </div>
        @endif

        @if($packages->isEmpty())
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm text-center text-gray-500 text-sm">
                No credit packages are currently available. Please check back later.
            </div>
        @else
            @if($reviewerMode ?? false)
                <div class="mb-5 rounded-2xl border border-amber-100 bg-amber-50 p-4 text-sm text-amber-800 shadow-sm">
                    <p class="font-semibold">Read-Only Mode — Sample Credit Packages</p>
                    <p class="mt-1">The packages below are shown for review purposes only. Checkout and payment are disabled for reviewer accounts.</p>
                </div>
            @endif
            {{-- Step 1: Package selection --}}
            <div x-show="step === 'select'" class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-6">
                <form id="package-form" action="{{ route('purchase-credit.checkout') }}" method="POST" class="space-y-5">
                    @csrf
                    <input type="hidden" name="provider_profile_id" value="{{ $activeProfile?->id }}">
                    <div x-show="paymentError" class="rounded-xl border border-rose-100 bg-rose-50 p-3 text-sm text-rose-700" x-text="paymentError"></div>
                    @if($lockedPackageId && $selectedPackage)
                        <input type="hidden" name="package_id" value="{{ $lockedPackageId }}">
                        <div class="rounded-xl border border-pink-100 bg-pink-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-[#e04ecb]">Selected membership</p>
                            <p class="mt-2 text-lg font-bold text-gray-900">{{ $selectedPackage->total_credits }} credits</p>
                            <p class="mt-1 text-sm text-gray-700">Up to {{ $selectedPackage->total_credits }} days online &mdash; AUD ${{ number_format($selectedPackage->price, 2) }} (incl. GST)</p>
                            @if($selectedPackage->description)
                                <p class="mt-1 text-xs text-gray-500">{{ $selectedPackage->description }}</p>
                            @elseif($selectedPackage->name)
                                <p class="mt-1 text-xs text-gray-500">{{ $selectedPackage->name }}</p>
                            @endif
                            <p class="mt-3 text-xs text-gray-500">This membership selection is locked for the current checkout.</p>
                            <a
                                href="{{ route('purchase-credit') }}"
                                class="mt-3 inline-flex text-xs font-semibold text-[#e04ecb] transition hover:text-[#c13ab0] hover:underline"
                            >
                                Add a fresh balance instead &rarr;
                            </a>
                        </div>
                    @else
                        <div class="rounded-xl border border-gray-100">
                            @foreach($packages as $index => $package)
                                <label class="flex cursor-pointer items-center justify-between gap-3 px-4 py-4 {{ $index < $packages->count() - 1 ? 'border-b border-gray-100' : '' }}">
                                    <div class="flex items-center gap-3">
                                        <input
                                            type="radio"
                                            name="package_id"
                                            value="{{ $package->id }}"
                                            class="h-4 w-4 border-gray-300 text-[#e04ecb] focus:ring-pink-200"
                                            @change="selectedPackageId = {{ $package->id }}"
                                            {{ $package->id === $selectedPackageId ? 'checked' : '' }}
                                         >
                                         <div>
                                            <p class="text-sm font-semibold text-gray-900">{{ $package->total_credits }} credits</p>
                                            <p class="text-xs text-gray-700">Up to {{ $package->total_credits }} days online &mdash; AUD ${{ number_format($package->price, 2) }} (incl. GST)</p>
                                            @if($package->bonus_credits > 0)
                                                <p class="text-xs font-medium text-emerald-600">Includes {{ $package->bonus_credits }} bonus credits</p>
                                            @endif
                                            @if($package->description)
                                                <p class="text-xs text-gray-500">{{ $package->description }}</p>
                                            @elseif($package->name)
                                                <p class="text-xs text-gray-500">{{ $package->name }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        @click="selectedPackageId = {{ $package->id }}; $el.closest('label').querySelector('input').checked = true"
                                        class="rounded-lg border border-[#e04ecb] px-3 py-1.5 text-xs font-semibold text-[#e04ecb] transition hover:bg-pink-50"
                                    >
                                        Select
                                    </button>
                                </label>
                            @endforeach
                        </div>
                    @endif

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        @if(!($guestMode ?? false))
                        <div class="lg:col-span-2">
                            <label for="invoice_name" class="mb-2 block text-sm font-semibold text-gray-700">
                                Invoice Name
                                <span class="font-normal text-gray-500">(displayed on invoice)</span>
                            </label>
                            <input
                                id="invoice_name"
                                name="invoice_name"
                                type="text"
                                value="{{ old('invoice_name', $userName) }}"
                                class="h-11 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 outline-none transition placeholder:text-gray-400 focus:border-pink-400 focus:ring-2 focus:ring-pink-100"
                            >
                        </div>
                        @endif

                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 {{ ($guestMode ?? false) ? 'md:col-span-2 lg:col-span-3' : '' }}">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Selected package</p>
                            <template x-if="selected">
                                <div>
                                    <p class="mt-2 text-xl font-bold text-gray-900" x-text="selected.credits + ' credits'"></p>
                                    <p class="mt-1 text-sm text-gray-700" x-text="'Up to ' + selected.credits + ' days online'"></p>
                                    <p class="mt-0.5 text-sm font-semibold text-gray-900" x-text="'AUD $' + selected.price"></p>
                                    <p class="mt-1 text-xs font-medium text-emerald-600" x-show="selected.bonus_credits > 0" x-text="'Includes ' + selected.bonus_credits + ' bonus credits'"></p>
                                    <p class="mt-1 text-xs text-gray-500" x-text="selected.name"></p>
                                </div>
                            </template>
                            <p class="mt-1 text-xs text-gray-500">Final payment is shown at checkout.</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                        <p class="text-xs text-gray-500">All prices are in Australian Dollars (AUD) and include GST.</p>
                        @if($guestMode ?? false)
                            <a
                                href="{{ route('signin') }}"
                                class="inline-flex h-11 items-center rounded-full bg-[#e04ecb] px-6 text-sm font-semibold text-white transition hover:bg-[#c13ab0]"
                            >
                                Sign in to purchase credits
                            </a>
                        @elseif(!($reviewerMode ?? false))
                            @if($paymentEnabled && ($checkoutEnabled ?? true) && $paymentProvider === 'stripe')
                                <button
                                    type="button"
                                    id="proceed-to-payment"
                                    @click="window.proceedToPayment($event)"
                                    :disabled="processing || !termsAccepted || !selectedPackageId"
                                    class="inline-flex h-11 items-center rounded-full bg-[#e04ecb] px-6 text-sm font-semibold text-white transition hover:bg-[#c13ab0] disabled:opacity-60 disabled:cursor-not-allowed"
                                >
                                    <span x-show="!processing">{{ ($stripeTestMode ?? false) ? 'Continue to test payment' : 'Continue to payment' }}</span>
                                    <span x-show="processing" class="flex items-center gap-2">
                                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        Processing&hellip;
                                    </span>
                                </button>
                            @elseif(!($checkoutEnabled ?? true))
                                <span class="inline-flex h-11 items-center rounded-full border border-amber-400 bg-amber-50 px-6 text-sm font-semibold text-amber-700 cursor-not-allowed opacity-75">
                                    Checkout disabled by admin
                                </span>
                            @else
                                <span class="inline-flex h-11 items-center rounded-full border border-amber-400 bg-amber-50 px-6 text-sm font-semibold text-amber-700 cursor-not-allowed opacity-75">
                                    Checkout unavailable
                                </span>
                            @endif
                        @else
                            <span class="inline-flex h-11 items-center rounded-full border border-amber-400 bg-amber-50 px-6 text-sm font-semibold text-amber-700 cursor-not-allowed opacity-75">
                                Checkout disabled (read-only)
                            </span>
                        @endif
                    </div>

                    {{-- Terms and policies --}}
                    <div class="border-t border-gray-100 pt-4 space-y-3">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input
                                type="checkbox"
                                x-model="termsAccepted"
                                class="mt-0.5 h-4 w-4 rounded border-gray-300 text-[#e04ecb] focus:ring-pink-200 shrink-0"
                            >
                            <span class="text-xs text-gray-600 leading-relaxed">
                                I have read and agree to the
                                <a href="{{ route('refund-policy') }}" target="_blank" class="font-medium text-[#e04ecb] hover:underline">Refund Policy</a>
                                and understand that credits are used for profile visibility and promotional listing features only.
                            </span>
                        </label>
                        <p class="text-xs text-gray-500">
                            By proceeding you acknowledge the
                            <a href="{{ route('credit-usage-and-expiry-policy') }}" target="_blank" class="text-[#e04ecb] hover:underline">Credit Usage &amp; Expiry Policy</a>
                            and
                            <a href="{{ route('terms-and-conditions') }}" target="_blank" class="text-[#e04ecb] hover:underline">Terms &amp; Conditions</a>.
                        </p>
                        <p class="text-xs text-gray-500">
                            For business/support contact details, please email support@hotescorts.com.au or use our
                            <a href="{{ route('contact-us') }}" class="text-[#e04ecb] hover:underline">contact us</a>
                            page.
                        </p>
                    </div>
                </form>
            </div>

            {{-- Step 2: payment provider element --}}
            @if($paymentEnabled && $paymentProvider === 'stripe' && !($reviewerMode ?? false))
            <div x-show="step === 'payment'" x-cloak class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-6 space-y-5">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Enter payment details</h2>
                    <button type="button" @click="step = 'select'; paymentError = null" class="text-xs font-medium text-[#e04ecb] hover:underline">&larr; Back</button>
                </div>

                <template x-if="selected">
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 text-sm text-gray-700">
                        <span class="font-semibold text-gray-900" x-text="selected.credits + ' credits'"></span>
                        &mdash;
                        <span x-text="'AUD $' + selected.price + ' (incl. GST)'"></span>
                    </div>
                </template>

                <div x-show="paymentError" class="rounded-xl border border-rose-100 bg-rose-50 p-3 text-sm text-rose-700" x-text="paymentError"></div>

                <div id="payment-element" class="rounded-xl border border-gray-200 p-4"></div>

                <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                    <p class="text-xs text-gray-500">Secured by {{ ucfirst($paymentProvider) }}. All prices in AUD include GST.</p>
                    <button
                        type="button"
                        id="submit-payment"
                        @click="window.submitPayment($event)"
                        :disabled="processing || !stripeReady"
                        class="inline-flex h-11 items-center rounded-full bg-[#e04ecb] px-6 text-sm font-semibold text-white transition hover:bg-[#c13ab0] disabled:opacity-60 disabled:cursor-not-allowed"
                    >
                        <span x-show="!processing">Pay now</span>
                        <span x-show="processing" class="flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Processing&hellip;
                        </span>
                    </button>
                </div>
            </div>
            @endif
        @endif
    </div>
</div>
@endsection

@if($paymentEnabled && $paymentProvider === 'stripe')
@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
(function () {
    const stripe = Stripe('{{ $paymentPublicKey }}');
    let elements = null;
    let paymentElement = null;
    let clientSecret = null;

    function getAlpineData() {
        const purchaseCreditFlow = document.getElementById('purchase-credit-flow');

        return purchaseCreditFlow ? Alpine.$data(purchaseCreditFlow) : null;
    }

    window.proceedToPayment = async function () {
        const data = getAlpineData();
        const form = document.getElementById('package-form');

        if (!data || !form) {
            return;
        }

        const invoiceName = form.querySelector('[name="invoice_name"]').value.trim();

        if (!invoiceName) {
            alert('Please enter an invoice name.');
            return;
        }

        if (!data.selectedPackageId) {
            alert('Please select a credit package.');
            return;
        }

        data.processing = true;
        data.paymentError = null;

        try {
            const csrf = form.querySelector('[name="_token"]').value;

            const response = await fetch('{{ route('purchase-credit.create-intent') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    package_id: data.selectedPackageId,
                    invoice_name: invoiceName,
                    provider_profile_id: {{ (int) ($activeProfile?->id ?? 0) }},
                }),
            });

            const result = await response.json();

            if (!response.ok || result.error) {
                data.paymentError = result.error || result.message || 'Failed to initialize payment. Please try again.';
                data.processing = false;
                return;
            }

            clientSecret = result.client_secret;
            if (!clientSecret) {
                data.paymentError = 'Failed to initialize payment. Please try again.';
                data.processing = false;
                return;
            }

            data.stripeReady = false;
            if (paymentElement) {
                paymentElement.unmount();
            }
            const container = document.getElementById('payment-element');
            if (container) {
                container.innerHTML = '';
            }

            elements = stripe.elements({ clientSecret });
            paymentElement = elements.create('payment');
            paymentElement.mount('#payment-element');

            paymentElement.on('ready', function () {
                data.stripeReady = true;
            });

            data.step = 'payment';
        } catch (err) {
            data.paymentError = 'An unexpected error occurred. Please try again.';
        } finally {
            data.processing = false;
        }
    };

    window.submitPayment = async function () {
        const data = getAlpineData();

        if (!data) {
            return;
        }

        data.processing = true;
        data.paymentError = null;

        const { error } = await stripe.confirmPayment({
            elements,
            confirmParams: {
                return_url: '{{ route('purchase-credit.success') }}',
            },
        });

        if (error) {
            data.paymentError = error.message ?? 'Payment failed. Please try again.';
            data.processing = false;
        }
    };
})();
</script>
@endpush
@endif
