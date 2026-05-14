@extends('layouts.frontend')

@section('content')
@php
    $packagesJson = $packages->map(fn ($p) => [
        'id' => $p->id,
        'name' => $p->name,
        'credits' => $p->credits,
        'price' => number_format($p->price, 2),
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
    get selected() {
        return this.packages.find(p => p.id === this.selectedPackageId) ?? null;
    }
}">
    <div class="mx-auto w-full max-w-5xl">
        <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">Add Balance</h1>
                <p class="mt-3 text-gray-600">One credit for every day your profile is online.</p>
            </div>
            <a href="{{ route('my-profile') }}" class="text-xs sm:text-sm font-medium text-[#e04ecb] transition hover:text-[#c13ab0] hover:underline">&larr; Back to dashboard</a>
        </div>

        <div class="mb-5">
            @include('subscription.partials.pricing-benefits', ['pricingPage' => $pricingPage ?? null])
        </div>

        <div class="mb-5 rounded-2xl border border-pink-100 bg-pink-50 p-4 text-xs sm:text-sm text-gray-700 shadow-sm">
            Your current credits balance is <span class="font-semibold text-gray-900">{{ $currentBalance }}</span>.
            You are charged <span class="font-semibold text-gray-900">1 credit per day</span> while your profile is visible.
        </div>

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

        @if($packages->isEmpty())
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm text-center text-gray-500 text-sm">
                No credit packages are currently available. Please check back later.
            </div>
        @else
            {{-- Step 1: Package selection --}}
            <div x-show="step === 'select'" class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-6">
                <form id="package-form" action="{{ route('purchase-credit.checkout') }}" method="POST" class="space-y-5">
                    @csrf
                    @if($lockedPackageId && $selectedPackage)
                        <input type="hidden" name="package_id" value="{{ $lockedPackageId }}">
                        <div class="rounded-xl border border-pink-100 bg-pink-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-[#e04ecb]">Selected membership</p>
                            <p class="mt-2 text-lg font-bold text-gray-900">{{ $selectedPackage->credits }} credits</p>
                            <p class="mt-1 text-sm text-gray-700">Up to {{ $selectedPackage->credits }} days online &mdash; AUD ${{ number_format($selectedPackage->price, 2) }} (incl. GST)</p>
                            @if($selectedPackage->description)
                                <p class="mt-1 text-xs text-gray-500">{{ $selectedPackage->description }}</p>
                            @elseif($selectedPackage->name)
                                <p class="mt-1 text-xs text-gray-500">{{ $selectedPackage->name }}</p>
                            @endif
                            <p class="mt-3 text-xs text-gray-500">This membership selection is locked for the current checkout.</p>
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
                                            <p class="text-sm font-semibold text-gray-900">{{ $package->credits }} credits</p>
                                            <p class="text-xs text-gray-700">Up to {{ $package->credits }} days online &mdash; AUD ${{ number_format($package->price, 2) }} (incl. GST)</p>
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

                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Selected package</p>
                            <template x-if="selected">
                                <div>
                                    <p class="mt-2 text-xl font-bold text-gray-900" x-text="selected.credits + ' credits'"></p>
                                    <p class="mt-1 text-sm text-gray-700" x-text="'Up to ' + selected.credits + ' days online'"></p>
                                    <p class="mt-0.5 text-sm font-semibold text-gray-900" x-text="'AUD $' + selected.price"></p>
                                    <p class="mt-1 text-xs text-gray-500" x-text="selected.name"></p>
                                </div>
                            </template>
                            <p class="mt-1 text-xs text-gray-500">Final payment is shown at checkout.</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                        <p class="text-xs text-gray-500">All prices are in Australian Dollars (AUD) and include GST.</p>
                        @if($stripeEnabled)
                            <button
                                type="button"
                                id="proceed-to-payment"
                                @click="proceedToPayment($event)"
                                :disabled="processing"
                                class="inline-flex h-11 items-center rounded-full bg-[#e04ecb] px-6 text-sm font-semibold text-white transition hover:bg-[#c13ab0] disabled:opacity-60 disabled:cursor-not-allowed"
                            >
                                <span x-show="!processing">Continue to payment</span>
                                <span x-show="processing" class="flex items-center gap-2">
                                    <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    Processing&hellip;
                                </span>
                            </button>
                        @else
                            <button type="submit" class="inline-flex h-11 items-center rounded-full bg-[#e04ecb] px-6 text-sm font-semibold text-white transition hover:bg-[#c13ab0]">
                                Continue to checkout
                            </button>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Step 2: Stripe PaymentElement (only rendered when stripe is enabled) --}}
            @if($stripeEnabled)
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
                    <p class="text-xs text-gray-500">Secured by Stripe. All prices in AUD include GST.</p>
                    <button
                        type="button"
                        id="submit-payment"
                        @click="submitPayment($event)"
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

@if($stripeEnabled)
@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
(function () {
    const stripe = Stripe('{{ $stripePublishableKey }}');
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
                }),
            });

            const result = await response.json();

            if (!response.ok || result.error) {
                data.paymentError = result.error || result.message || 'Failed to initialize payment. Please try again.';
                data.processing = false;
                return;
            }

            clientSecret = result.client_secret;

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
