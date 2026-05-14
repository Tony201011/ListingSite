@extends('layouts.frontend')

@section('content')
<div
    class="min-h-screen bg-gray-50 px-4 py-10 sm:px-6 lg:px-8"
    x-data="featuredPurchase({
        initialIsFeatured: @js((bool) $isFeatured),
        initialExpiresAt: @js($expiresAt ?? null),
        initialUserCredits: @js((int) $userCredits),
        creditCost: @js((int) $creditCost),
        durationDays: @js((int) $durationDays),
        purchaseUrl: @js(route('featured.purchase'))
    })"
>
    <div class="mx-auto max-w-3xl">
        @include('profile.partials.back-to-settings')

        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
            <div class="mb-4 flex items-center gap-3">
                <div
                    class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold"
                    :class="isFeatured ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600'"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    <span x-text="isFeatured ? 'Featured Active' : 'Not Featured'"></span>
                </div>
            </div>

            <h1 class="mb-3 text-2xl font-bold text-gray-900 sm:text-3xl">
                Featured Listing
            </h1>

            <p class="mb-6 text-gray-600">
                Boost your profile by activating Featured status. Featured listings appear prominently on the site and attract more visibility.
            </p>

            <div class="mb-6 grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                    <p class="text-sm font-medium text-gray-500">Credit cost</p>
                    <p class="mt-2 text-2xl font-bold text-pink-600" x-text="creditCost + ' ' + (creditCost === 1 ? 'credit' : 'credits')"></p>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                    <p class="text-sm font-medium text-gray-500">Duration</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900" x-text="durationDays + ' ' + (durationDays === 1 ? 'day' : 'days')"></p>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                    <p class="text-sm font-medium text-gray-500">Your credits</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900" x-text="userCredits"></p>
                </div>
            </div>

            <div
                x-show="isFeatured && expiresAt"
                x-cloak
                x-transition
                class="mb-6 rounded-2xl border border-yellow-100 bg-yellow-50 p-4"
            >
                <p class="text-sm font-medium text-yellow-700">Featured expires</p>
                <p class="mt-1 text-base font-semibold text-yellow-900" x-text="formattedExpiry"></p>
                <p class="mt-1 text-sm text-yellow-700">Purchasing again will extend your featured period by <span x-text="durationDays"></span> more days.</p>
            </div>

            <button
                type="button"
                @click="purchase()"
                :disabled="loading || userCredits < creditCost"
                class="flex w-full items-center justify-center gap-2 rounded-lg px-5 py-2.5 font-semibold transition sm:w-auto"
                :class="userCredits >= creditCost
                    ? 'bg-yellow-500 text-white hover:bg-yellow-600'
                    : 'cursor-not-allowed bg-gray-100 text-gray-400 opacity-50'"
            >
                <span x-show="!loading" x-cloak>
                    <span x-text="isFeatured ? 'Extend Featured' : 'Activate Featured'"></span>
                    <span x-text="' (' + creditCost + ' ' + (creditCost === 1 ? 'credit' : 'credits') + ')'"></span>
                </span>

                <span x-show="loading" x-cloak class="flex items-center gap-2">
                    <svg
                        class="h-4 w-4 animate-spin"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processing...
                </span>
            </button>

            <template x-if="message">
                <div
                    class="mt-4 text-sm"
                    :class="messageType === 'success' ? 'text-green-600' : 'text-red-600'"
                    x-text="message"
                ></div>
            </template>

            <div
                x-show="userCredits < creditCost"
                x-cloak
                x-transition
                class="mt-6 rounded-2xl border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800"
            >
                You need <span class="font-semibold" x-text="creditCost"></span> credits to activate Featured. You currently have <span class="font-semibold" x-text="userCredits"></span>.
                <a href="{{ route('purchase-credit') }}" class="ml-1 font-semibold text-pink-600 underline hover:text-pink-700">Purchase credits</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('profile/js/featured-purchase.js') }}"></script>
@endpush
