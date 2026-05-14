@extends('layouts.frontend')

@section('content')
<div
    class="min-h-screen bg-gray-50 px-4 py-10 sm:px-6 lg:px-8"
    x-data="adTierPurchase({
        initialUserCredits: @js((int) $userCredits),
        durationDays: @js((int) $durationDays),
        purchaseUrl: @js(route('featured.purchase')),
        tiers: @js([
            [
                'key'        => 'home_banner',
                'label'      => 'Home Page Banner',
                'subtitle'   => 'National — shown in the banner strip at the top of the home page (all Australia)',
                'cost'       => (int) $settings['home_banner_credit_cost'],
                'expiresAt'  => $homeBannerExpiresAt,
                'colorClass' => 'from-purple-500 to-indigo-600',
                'badgeClass' => 'bg-indigo-100 text-indigo-700',
                'icon'       => '🏆',
            ],
            [
                'key'        => 'home_page',
                'label'      => 'Home Page Featured',
                'subtitle'   => 'Show at the top of the home page listing grid — visible even when offline',
                'cost'       => (int) $settings['home_featured_credit_cost'],
                'expiresAt'  => $homeFeaturedExpiresAt,
                'colorClass' => 'from-pink-500 to-rose-500',
                'badgeClass' => 'bg-pink-100 text-pink-700',
                'icon'       => '🌟',
            ],
            [
                'key'        => 'local_banner',
                'label'      => 'Local Banner',
                'subtitle'   => 'State-specific — shown in the banner strip on your local area page',
                'cost'       => (int) $settings['local_banner_credit_cost'],
                'expiresAt'  => $localBannerExpiresAt,
                'colorClass' => 'from-amber-500 to-orange-500',
                'badgeClass' => 'bg-amber-100 text-amber-700',
                'icon'       => '📍',
            ],
            [
                'key'        => 'normal',
                'label'      => 'Featured Badge',
                'subtitle'   => 'Featured star badge on your profile card in the listing grid',
                'cost'       => (int) $settings['normal_featured_credit_cost'],
                'expiresAt'  => $expiresAt,
                'colorClass' => 'from-yellow-400 to-amber-500',
                'badgeClass' => 'bg-yellow-100 text-yellow-700',
                'icon'       => '⭐',
            ],
        ])
    })"
>
    <div class="mx-auto max-w-4xl">
        @include('profile.partials.back-to-settings')

        <div class="mb-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
            <h1 class="mb-2 text-2xl font-bold text-gray-900 sm:text-3xl">Boost Your Profile</h1>
            <p class="text-gray-600">Choose one or more ad placements to increase your visibility. Each placement is purchased for <strong x-text="durationDays"></strong> days.</p>

            <div class="mt-4 flex items-center gap-3 rounded-xl bg-gray-50 px-4 py-3">
                <span class="text-sm text-gray-500">Your credit balance:</span>
                <span class="text-lg font-bold text-gray-900" x-text="userCredits + ' credits'"></span>
                <a href="{{ route('purchase-credit') }}" class="ml-auto text-xs font-semibold text-pink-600 underline hover:text-pink-700">Buy credits</a>
            </div>
        </div>

        {{-- Free listing notice --}}
        @if($freeListingExpiresAt)
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                <i class="fa-solid fa-gift mr-1 text-emerald-600"></i>
                <strong>Free listing active!</strong> Your listing is free until
                <strong>{{ \Carbon\Carbon::parse($freeListingExpiresAt)->format('d M Y') }}</strong>.
                After that, 1 credit per day keeps your listing visible.
            </div>
        @endif

        <div class="grid gap-5 sm:grid-cols-2">
            <template x-for="(tier, index) in tiers" :key="tier.key">
                <div class="relative rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md"
                     :class="tier.expiresAt && new Date(tier.expiresAt) > new Date() ? 'ring-2 ring-green-400' : ''">
                    {{-- Active badge --}}
                    <template x-if="tier.expiresAt && new Date(tier.expiresAt) > new Date()">
                        <span class="absolute right-3 top-3 rounded-full bg-green-100 px-2.5 py-0.5 text-[11px] font-semibold text-green-700">Active</span>
                    </template>

                    <div class="mb-3 flex items-center gap-3">
                        <span class="text-2xl" x-text="tier.icon"></span>
                        <div>
                            <h2 class="text-base font-bold text-gray-900" x-text="tier.label"></h2>
                            <p class="text-xs text-gray-500" x-text="tier.subtitle"></p>
                        </div>
                    </div>

                    <div class="mb-4 flex items-end gap-2">
                        <span class="text-2xl font-extrabold text-gray-900" x-text="tier.cost"></span>
                        <span class="mb-0.5 text-sm text-gray-500">credits / <span x-text="durationDays + ' days'"></span></span>
                    </div>

                    <template x-if="tier.expiresAt && new Date(tier.expiresAt) > new Date()">
                        <p class="mb-3 text-xs text-green-700">
                            Expires: <span x-text="new Date(tier.expiresAt).toLocaleDateString(undefined, { year:'numeric', month:'long', day:'numeric' })"></span>
                        </p>
                    </template>

                    <button
                        type="button"
                        @click="purchase(tier)"
                        :disabled="loading === tier.key || userCredits < tier.cost"
                        class="flex w-full items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold text-white transition"
                        :class="[
                            userCredits >= tier.cost ? 'bg-gradient-to-r ' + tier.colorClass + ' hover:opacity-90 active:scale-95' : 'cursor-not-allowed bg-gray-200 text-gray-400',
                            loading === tier.key ? 'opacity-70' : ''
                        ]"
                    >
                        <template x-if="loading !== tier.key">
                            <span x-text="(tier.expiresAt && new Date(tier.expiresAt) > new Date()) ? 'Extend (' + tier.cost + ' credits)' : 'Activate (' + tier.cost + ' credits)'"></span>
                        </template>
                        <template x-if="loading === tier.key">
                            <span class="flex items-center gap-2">
                                <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                        </template>
                    </button>

                    <template x-if="messages[tier.key]">
                        <p class="mt-2 text-xs" :class="messageTypes[tier.key] === 'success' ? 'text-green-600' : 'text-red-600'" x-text="messages[tier.key]"></p>
                    </template>
                </div>
            </template>
        </div>

        <div class="mt-6 rounded-2xl border border-gray-100 bg-white p-5 text-sm text-gray-600 shadow-sm">
            <h3 class="mb-2 font-semibold text-gray-800">How it works</h3>
            <ul class="list-inside list-disc space-y-1">
                <li><strong>Free for {{ $settings['free_listing_days'] }} days</strong> — new listings are free for the first {{ $settings['free_listing_days'] }} days.</li>
                <li><strong>1 credit / day</strong> after the free period to keep your listing visible.</li>
                <li>Each ad placement is purchased for <strong x-text="durationDays"></strong> days. Purchasing again extends the active period.</li>
                <li>Credits can be purchased on the <a href="{{ route('purchase-credit') }}" class="font-semibold text-pink-600 underline hover:text-pink-700">credits page</a>.</li>
            </ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('profile/js/featured-purchase.js') }}"></script>
@endpush
