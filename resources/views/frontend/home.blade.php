@extends('layouts.frontend')

@section('title', 'Home')

@php
    $selectedCategoryIds = collect($selectedCategoryIds ?? []);
    $locationQuery = (string) ($locationQuery ?? '');
    $escortNameQuery = (string) ($escortNameQuery ?? '');
    $hasAgeFilter = $hasAgeFilter ?? false;
    $hasPriceFilter = $hasPriceFilter ?? false;
    $hasDistanceFilter = $hasDistanceFilter ?? false;
    $distanceSearchEnabled = $distanceSearchEnabled ?? true;
    $maxSearchDistance = (int) ($maxSearchDistance ?? 500);
    $distanceFilter = (int) ($distanceFilter ?? $maxSearchDistance);
    $userLat = $userLat ?? null;
    $userLng = $userLng ?? null;
    $girlsMode = (string) ($girlsMode ?? 'all');
    $selectedCategoryItems = $selectedCategoryItems ?? collect();
    $hasActiveFilters = $locationQuery !== '' || $escortNameQuery !== '' || collect($selectedCategoryItems)->isNotEmpty() || $hasAgeFilter || $hasPriceFilter || $hasDistanceFilter;
@endphp

@section('content')
<div class="min-h-screen bg-gray-100 text-gray-800">

    {{-- Search Bar --}}
    <div class="bg-gray-950 border-b border-gray-800">
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
            <div x-data="escortSearch({
                    initialMode: '{{ $escortNameQuery !== '' ? 'username' : 'suburb' }}',
                    initialTerm: {!! Js::from($escortNameQuery !== '' ? $escortNameQuery : $locationQuery) !!},
                    suggestionsUrl: '{{ route('api.search.suggestions') }}',
                    suburbSuggestionsUrl: '{{ route('api.suburbs.search') }}',
                    userLat: '{{ $userLat ?? '' }}',
                    userLng: '{{ $userLng ?? '' }}',
                    distance: {{ Js::from($distanceFilter ?? $maxSearchDistance) }},
                    maxDistance: {{ Js::from($maxSearchDistance) }},
                    locationEnabled: {{ ($distanceSearchEnabled && $userLat !== null && $userLng !== null) ? 'true' : 'false' }},
                    distanceSearchEnabled: {{ $distanceSearchEnabled ? 'true' : 'false' }}
                })" x-init="if (distanceSearchEnabled && !locationEnabled) requestLocation()" @keydown.escape="closeSuggestions()" @click.outside="closeSuggestions()">
                <form method="GET" action="{{ route('escorts.search') }}" @submit="handleFormSubmit($event)">
                    <template x-if="distanceSearchEnabled">
                        <span>
                            <template x-if="searchMode === 'suburb' && (locationEnabled || term.trim() !== '')">
                                <input type="hidden" name="distance" :value="distance">
                            </template>
                        </span>
                    </template>

                    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                        {{-- Text input --}}
                        <div class="relative min-w-0 flex-1">
                            <input
                                type="text"
                                :name="searchMode === 'suburb' ? 'location' : 'escort_name'"
                                x-model="term"
                                @input.debounce.300ms="fetchSuggestions()"
                                @focus="fetchSuggestions()"
                                @keydown.arrow-down.prevent="highlightNext()"
                                @keydown.arrow-up.prevent="highlightPrev()"
                                @keydown.enter.prevent="selectHighlighted($event)"
                                :placeholder="searchMode === 'username' ? 'Search by escort name…' : 'Enter a location to find local escorts'"
                                class="w-full rounded border-0 bg-white py-3 px-4 text-sm text-gray-900 placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-fuchsia-500"
                                autocomplete="off"
                            >

                            {{-- Suggestions dropdown --}}
                            <div
                                x-show="showSuggestions && suggestions.length > 0"
                                x-cloak
                                class="absolute left-0 right-0 top-full z-50 mt-1 overflow-hidden rounded border border-gray-700 bg-gray-900 shadow-xl"
                            >
                                <ul class="divide-y divide-gray-800">
                                    <template x-for="(item, index) in suggestions" :key="index">
                                        <li>
                                            <button
                                                type="button"
                                                @click="selectSuggestion(item, $event)"
                                                class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm transition"
                                                :class="index === highlightedIndex ? 'bg-pink-600/20 text-pink-300' : 'text-gray-200 hover:bg-gray-800'"
                                                @mouseenter="highlightedIndex = index"
                                                @mouseleave="highlightedIndex = -1"
                                            >
                                                <i :class="item.type === 'suburb' ? 'fa-solid fa-location-dot' : 'fa-solid fa-user'" class="text-gray-500 text-xs shrink-0"></i>
                                                <span class="truncate" x-text="item.name"></span>
                                                <span class="ml-auto shrink-0 text-xs text-gray-500" x-show="item.type === 'suburb' && item.label" x-text="item.label"></span>
                                            </button>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>

                        {{-- Find Escorts button --}}
                        <button type="submit" class="w-full shrink-0 rounded bg-fuchsia-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-fuchsia-700 active:scale-95 sm:w-auto">
                            Find Escorts
                        </button>

                        {{-- Advanced search link --}}
                        <a href="{{ route('advanced-search') }}" class="w-full shrink-0 text-center text-sm font-medium text-fuchsia-400 transition hover:text-fuchsia-300 sm:w-auto sm:text-left">
                            Advanced search
                        </a>

                        {{-- Search by Name / Search by Location toggle --}}
                        <button
                            type="button"
                            @click="searchMode = searchMode === 'suburb' ? 'username' : 'suburb'; term = ''; closeSuggestions()"
                            class="w-full shrink-0 rounded bg-gray-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-gray-600 sm:w-auto"
                            x-text="searchMode === 'suburb' ? 'Search by Name' : 'Search by Location'"
                        ></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"
        x-data="favouriteBookmark({
            favourites: {{ Js::from($userFavourites ?? []) }}
        })"
    >

        <div id="listings-content" x-cloak>
        {{-- Toolbar: filters, sort, view toggle --}}
        <div class="mb-5 flex flex-wrap items-center gap-3 border-b border-gray-200 pb-4">
            @php
                $currentQuery = request()->query();
                $newGirlsQuery = array_merge($currentQuery, ['girls' => 'new']);
                $allGirlsQuery = array_merge($currentQuery, ['girls' => 'all']);
                $popularGirlsQuery = array_merge($currentQuery, ['girls' => 'popular']);
                $girlsUrl = fn (array $query): string => url()->current().(! empty($query) ? '?'.http_build_query($query) : '');
            @endphp
            <div class="flex flex-wrap items-center gap-2">
                <a
                    href="{{ $girlsUrl($newGirlsQuery) }}"
                    class="rounded-full border px-4 py-1.5 text-xs font-semibold transition {{ $girlsMode === 'new' ? 'border-pink-600 bg-pink-600/10 text-pink-600' : 'border-gray-300 bg-white text-gray-600 hover:border-pink-300 hover:text-pink-600' }}"
                >
                    New girls
                </a>
                <a
                    href="{{ $girlsUrl($allGirlsQuery) }}"
                    class="rounded-full border px-4 py-1.5 text-xs font-semibold transition {{ $girlsMode === 'all' ? 'border-pink-600 bg-pink-600/10 text-pink-600' : 'border-gray-300 bg-white text-gray-600 hover:border-pink-300 hover:text-pink-600' }}"
                >
                    All girls
                </a>
                <a
                    href="{{ $girlsUrl($popularGirlsQuery) }}"
                    class="inline-flex items-center gap-1 rounded-full border px-4 py-1.5 text-xs font-semibold transition {{ $girlsMode === 'popular' ? 'border-pink-600 bg-pink-600/10 text-pink-600' : 'border-gray-300 bg-white text-gray-600 hover:border-pink-300 hover:text-pink-600' }}"
                >
                    <i class="fa-solid fa-fire text-[10px]"></i>
                    Popular
                </a>
            </div>

            {{-- Online users counter --}}
            @if(($onlineCount ?? 0) > 0)
                <span class="ml-auto inline-flex items-center gap-1.5 rounded-full bg-green-50 border border-green-200 px-3 py-1 text-xs font-semibold text-green-700">
                    <span class="inline-block h-2 w-2 rounded-full bg-green-500"></span>
                    {{ $onlineCount }} online {{ $onlineCount === 1 ? 'user' : 'users' }}
                </span>
            @endif
        </div>

        {{-- Active filter pills --}}
        @if($hasActiveFilters)
            <div class="mb-4 flex flex-wrap gap-2">
                @if($locationQuery !== '')
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white border border-gray-300 px-3 py-1 text-xs text-gray-700">
                        <i class="fa-solid fa-location-dot text-pink-500 text-[10px]"></i> {{ $locationQuery }}
                    </span>
                @endif
                @if($escortNameQuery !== '')
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white border border-gray-300 px-3 py-1 text-xs text-gray-700">
                        <i class="fa-solid fa-user text-pink-500 text-[10px]"></i> {{ $escortNameQuery }}
                    </span>
                @endif
                @foreach(collect($selectedCategoryItems) as $item)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white border border-gray-300 px-3 py-1 text-xs text-gray-700">
                        {{ $item['name'] }}
                    </span>
                @endforeach
                @if($hasDistanceFilter)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white border border-gray-300 px-3 py-1 text-xs text-gray-700">
                        <i class="fa-solid fa-location-crosshairs text-pink-500 text-[10px]"></i> Within {{ $distanceFilter }} km
                    </span>
                @endif
            </div>
        @endif

        {{-- Ad: Home Top --}}
        @include('layouts.partials.ads', ['position' => 'home_top', 'pageKey' => 'home'])

        {{-- Home Banner: Paid featured profiles (national, $5/day) --}}
        {{-- Hidden when a local banner is active OR when a location filter is active --}}
        @if(!empty($homeBannerProfiles) && count($homeBannerProfiles) > 0 && $locationQuery === '' && ($locationStateQuery ?? '') === '' && (empty($localBannerProfiles) || count($localBannerProfiles) === 0))
            <div class="mb-6">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-purple-600 to-indigo-600 px-3 py-1 text-xs font-bold uppercase tracking-wider text-white shadow">
                        <i class="fa-solid fa-crown text-[10px]"></i> Featured
                    </span>
                </div>
                <div class="featured-slider" data-featured-slider>
                    <div class="featured-slider-track" data-slider-track tabindex="0">
                        @foreach($homeBannerProfiles as $profile)
                            <div class="featured-slider-slide">
                                @include('frontend.partials.profile-card', ['profile' => $profile, 'tierBadgeVariant' => 'home_banner'])
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="featured-slider-button" data-slider-prev aria-label="Previous featured profiles">
                        <i class="fa-solid fa-chevron-left text-lg"></i>
                    </button>
                    <button type="button" class="featured-slider-button" data-slider-next aria-label="Next featured profiles">
                        <i class="fa-solid fa-chevron-right text-lg"></i>
                    </button>
                </div>
            </div>
            <hr class="mb-6 border-gray-200">
        @endif

        {{-- Local Banner: Paid state-specific featured profiles ($2/day) --}}
        {{-- NOTE: shown even when a location filter is active, since localBannerProfiles is only --}}
        {{-- populated when a location with a state abbreviation is present in the URL. --}}
        @if(!empty($localBannerProfiles) && count($localBannerProfiles) > 0)
            <div class="mb-6">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-amber-500 to-orange-500 px-3 py-1 text-xs font-bold uppercase tracking-wider text-white shadow">
                        <i class="fa-solid fa-location-dot text-[10px]"></i> Local Featured
                    </span>
                </div>
                <div class="featured-slider" data-featured-slider>
                    <div class="featured-slider-track" data-slider-track tabindex="0">
                        @foreach($localBannerProfiles as $profile)
                            <div class="featured-slider-slide">
                                @include('frontend.partials.profile-card', ['profile' => $profile, 'tierBadgeVariant' => 'local_banner'])
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="featured-slider-button" data-slider-prev aria-label="Previous local featured profiles">
                        <i class="fa-solid fa-chevron-left text-lg"></i>
                    </button>
                    <button type="button" class="featured-slider-button" data-slider-next aria-label="Next local featured profiles">
                        <i class="fa-solid fa-chevron-right text-lg"></i>
                    </button>
                </div>
            </div>
            <hr class="mb-6 border-gray-200">
        @endif

        {{-- Profile Cards Grid --}}
        <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
            @forelse($profiles as $profile)
                @if($loop->iteration === 11 && $loop->count > 10)
                    {{-- Ad: Between listings (shown as a full-width row after the 10th card) --}}
                    <div class="col-span-full">
                        @include('layouts.partials.ads', ['position' => 'home_between', 'pageKey' => 'home'])
                    </div>
                @endif
                @include('frontend.partials.profile-card', ['profile' => $profile])
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-white p-12 text-center">
                    <i class="fa-solid fa-magnifying-glass mb-4 text-3xl text-gray-400"></i>
                    <p class="text-sm font-medium text-gray-600">No profiles found matching your criteria.</p>
                    @if($hasActiveFilters)
                        <a href="{{ url('/') }}" class="mt-4 inline-block rounded-lg bg-pink-600 px-5 py-2 text-sm font-semibold text-white hover:bg-pink-700 transition">Clear filters</a>
                    @endif
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-10">
            @if($hasActiveFilters)
                <p class="mb-3 text-center text-sm text-gray-500">
                    <a href="{{ url('/') }}" class="text-pink-500 hover:text-pink-400 underline underline-offset-2">Clear filters</a>
                </p>
            @endif
            {{ $profiles->links() }}
        </div>

        {{-- Ad: Home Bottom --}}
        @include('layouts.partials.ads', ['position' => 'home_bottom', 'pageKey' => 'home'])
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/css/home.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('frontend/js/home.js') }}"></script>
<script src="{{ asset('profile/js/profile-online-sync.js') }}?v={{ filemtime(public_path('profile/js/profile-online-sync.js')) }}"></script>
<script src="{{ asset('frontend/js/listings-refresh.js') }}?v={{ filemtime(public_path('frontend/js/listings-refresh.js')) }}"></script>
@endpush
