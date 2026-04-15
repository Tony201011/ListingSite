@extends('layouts.frontend')

@section('title', 'Home')

@php
    $selectedCategoryIds = collect($selectedCategoryIds ?? []);
    $locationQuery = (string) ($locationQuery ?? '');
    $escortNameQuery = (string) ($escortNameQuery ?? '');
    $hasAgeFilter = $hasAgeFilter ?? false;
    $hasPriceFilter = $hasPriceFilter ?? false;
    $hasDistanceFilter = $hasDistanceFilter ?? false;
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

    {{-- Hero / Search Section --}}
    <div class="relative overflow-x-hidden bg-gradient-to-b from-gray-950 via-gray-900 to-gray-900">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_rgba(236,72,153,0.15)_0%,_transparent_65%)] pointer-events-none"></div>
        <div class="relative mx-auto max-w-4xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8 text-center">
            <h1 class="mb-2 text-3xl font-extrabold tracking-tight text-white sm:text-4xl lg:text-5xl">
                Popular <span class="text-pink-500">Hot</span>escorts
            </h1>
            <p class="mb-8 text-sm text-gray-400 tracking-widest uppercase">100% Real &amp; Genuine Escorts · Australia-Wide</p>

            <div x-data="Object.assign(escortSearch({
                    initialMode: '{{ $escortNameQuery !== '' ? 'username' : 'suburb' }}',
                    initialTerm: '{{ e($escortNameQuery !== '' ? $escortNameQuery : $locationQuery) }}',
                    suggestionsUrl: '{{ route('api.search.suggestions') }}',
                    suburbSuggestionsUrl: '{{ route('api.suburbs.search') }}'
                }), {
                    userLat: '{{ $userLat ?? '' }}',
                    userLng: '{{ $userLng ?? '' }}',
                    distance: {{ $distanceFilter }},
                    maxDistance: {{ $maxSearchDistance }},
                    locationEnabled: {{ ($userLat !== null && $userLng !== null) ? 'true' : 'false' }},
                    geoError: '',
                    requestLocation() {
                        this.geoError = '';
                        if (!navigator.geolocation) {
                            this.geoError = 'Geolocation not supported.';
                            return;
                        }
                        navigator.geolocation.getCurrentPosition(
                            (pos) => {
                                this.userLat = pos.coords.latitude;
                                this.userLng = pos.coords.longitude;
                                this.locationEnabled = true;
                            },
                            (err) => {
                                if (err.code === 1) {
                                    this.geoError = 'Location access denied. Please allow location in your browser settings and try again.';
                                } else if (err.code === 2) {
                                    this.geoError = 'Location unavailable. Please check your device location settings.';
                                } else if (err.code === 3) {
                                    this.geoError = 'Location request timed out. Please try again.';
                                } else {
                                    this.geoError = 'Unable to get location. Please allow access.';
                                }
                            },
                            { timeout: 10000, maximumAge: 60000 }
                        );
                    },
                    clearLocation() {
                        this.userLat = '';
                        this.userLng = '';
                        this.locationEnabled = false;
                    }
                })" @keydown.escape="closeSuggestions()" @click.outside="closeSuggestions()">
                <form method="GET" action="{{ url('/') }}" @submit="closeSuggestions()">
                    <input type="hidden" name="location" :value="searchMode === 'suburb' ? term : ''">
                    <input type="hidden" name="escort_name" :value="searchMode === 'username' ? term : ''">
                    <input type="hidden" name="user_lat" :value="userLat">
                    <input type="hidden" name="user_lng" :value="userLng">
                    <input type="hidden" name="distance" :value="locationEnabled ? distance : ''">

                    <div class="mx-auto flex max-w-2xl flex-col gap-3 sm:flex-row sm:items-center">
                        <div class="relative flex-1">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                            <input
                                type="text"
                                x-model="term"
                                @input.debounce.300ms="fetchSuggestions()"
                                @focus="fetchSuggestions()"
                                @keydown.arrow-down.prevent="highlightNext()"
                                @keydown.arrow-up.prevent="highlightPrev()"
                                @keydown.enter.prevent="selectHighlighted($event)"
                                :placeholder="searchMode === 'username' ? 'Search by escort name…' : 'Search by suburb or city…'"
                                class="w-full rounded-xl border border-gray-700 bg-gray-800/80 py-3 pl-10 pr-4 text-sm text-white placeholder:text-gray-500 focus:border-pink-500 focus:outline-none focus:ring-1 focus:ring-pink-500/50"
                                autocomplete="off"
                            >

                            {{-- Suggestions dropdown --}}
                            <div
                                x-show="showSuggestions && suggestions.length > 0"
                                x-cloak
                                class="absolute left-0 right-0 top-full z-50 mt-1 overflow-hidden rounded-xl border border-gray-700 bg-gray-900 shadow-xl"
                            >
                                <ul class="divide-y divide-gray-800">
                                    <template x-for="(item, index) in suggestions" :key="index">
                                        <li>
                                            <a
                                                :href="item.type === 'profile' ? '/profile/' + item.slug : '#'"
                                                @click.prevent="selectSuggestion(item, $event)"
                                                class="flex items-center gap-3 px-4 py-2.5 text-left text-sm transition"
                                                :class="index === highlightedIndex ? 'bg-pink-600/20 text-pink-300' : 'text-gray-200 hover:bg-gray-800'"
                                                @mouseenter="highlightedIndex = index"
                                                @mouseleave="highlightedIndex = -1"
                                            >
                                                <i :class="item.type === 'suburb' ? 'fa-solid fa-location-dot' : 'fa-solid fa-user'" class="text-gray-500 text-xs shrink-0"></i>
                                                <span class="truncate" x-text="item.name"></span>
                                                <span class="ml-auto shrink-0 text-xs text-gray-500" x-show="item.label" x-text="item.label"></span>
                                                <span class="shrink-0 text-xs text-gray-600" x-show="item.type === 'profile' && item.age" x-text="item.age + 'y'"></span>
                                            </a>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                        <button type="submit" class="shrink-0 rounded-xl bg-pink-600 px-7 py-3 text-sm font-semibold text-white shadow-lg shadow-pink-900/40 transition hover:bg-pink-700 active:scale-95">
                            Find Escort
                        </button>
                    </div>

                    <div class="mt-3 flex flex-wrap justify-center gap-2">
                        <button
                            type="button"
                            @click="searchMode = searchMode === 'suburb' ? 'username' : 'suburb'; term = ''; closeSuggestions()"
                            class="rounded-lg border border-gray-700 bg-gray-800 px-4 py-2 text-xs font-medium text-gray-300 transition hover:border-pink-500 hover:text-pink-400"
                            x-text="searchMode === 'suburb' ? '🔎 Search by Name' : '📍 Search by Suburb'"
                        ></button>
                        <a href="{{ route('advanced-search') }}" class="rounded-lg border border-pink-700/50 bg-pink-600/10 px-4 py-2 text-xs font-medium text-pink-400 transition hover:bg-pink-600/20 hover:text-pink-300">
                            <i class="fa-solid fa-sliders mr-1"></i> Advanced Search / Filter
                        </a>
                    </div>

                    {{-- Near Me / Distance filter --}}
                    <div class="mt-3 flex flex-wrap items-center justify-center gap-2">
                        <button
                            type="button"
                            x-show="!locationEnabled"
                            @click="requestLocation()"
                            class="rounded-lg border border-gray-700 bg-gray-800 px-4 py-2 text-xs font-medium text-gray-300 transition hover:border-pink-500 hover:text-pink-400"
                        >
                            <i class="fa-solid fa-location-crosshairs mr-1 text-pink-400"></i> Near Me
                        </button>
                        <div x-show="locationEnabled" x-cloak class="flex flex-wrap items-center justify-center gap-2">
                            <span class="text-xs text-green-400">
                                <i class="fa-solid fa-circle-check text-[10px]"></i> Within <strong x-text="distance"></strong> km
                            </span>
                            <input
                                type="range"
                                min="0"
                                :max="maxDistance"
                                step="1"
                                x-model.number="distance"
                                class="w-32"
                            >
                            <button
                                type="button"
                                @click="clearLocation()"
                                class="rounded-lg border border-gray-700 bg-gray-800 px-3 py-1.5 text-xs text-gray-400 hover:text-gray-200"
                            >
                                <i class="fa-solid fa-xmark text-[10px]"></i>
                            </button>
                        </div>
                        <span x-show="geoError" x-cloak class="text-xs text-red-400" x-text="geoError"></span>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"
        x-data="favouriteBookmark({
            favourites: {{ Js::from($userFavourites ?? []) }},
            bookmarks: {{ Js::from($userBookmarks ?? []) }}
        })"
    >

        {{-- Toolbar: filters, sort, view toggle --}}
        <div class="mb-5 flex flex-wrap items-center gap-3 border-b border-gray-200 pb-4">
            @php
                $currentQuery = request()->query();
                $newGirlsQuery = array_merge($currentQuery, ['girls' => 'new']);
                $allGirlsQuery = array_merge($currentQuery, ['girls' => 'all']);
                $popularGirlsQuery = array_merge($currentQuery, ['girls' => 'popular']);
                $girlsUrl = fn (array $query): string => url('/').'?'.http_build_query($query);
            @endphp
            <div class="flex items-center gap-2">
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

            <div class="ml-auto flex items-center gap-2 text-xs text-gray-600">
                <button
                    type="button"
                    class="rounded-lg border px-2.5 py-1.5 transition"
                    :class="viewMode === 'list' ? 'border-pink-600 bg-pink-600/10 text-pink-600' : 'border-gray-300 text-gray-500 hover:border-gray-400 hover:text-gray-700'"
                    @click="viewMode = 'list'"
                    title="List view"
                >
                    <i class="fa-solid fa-list text-xs"></i>
                </button>
                <button
                    type="button"
                    class="rounded-lg border px-2.5 py-1.5 transition"
                    :class="viewMode === 'grid' ? 'border-pink-600 bg-pink-600/10 text-pink-600' : 'border-gray-300 text-gray-500 hover:border-gray-400 hover:text-gray-700'"
                    @click="viewMode = 'grid'"
                    title="Grid view"
                >
                    <i class="fa-solid fa-table-cells text-xs"></i>
                </button>
            </div>
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

        {{-- Profile Cards Grid / List --}}
        <div x-cloak class="grid gap-4" :class="viewMode === 'grid' ? 'sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5' : 'grid-cols-1'">
            @forelse($profiles as $profile)
                <article
                    class="view-card group relative overflow-hidden rounded-2xl bg-white shadow-sm border border-gray-200 transition-all duration-300 hover:shadow-md hover:border-gray-300"
                    :class="viewMode === 'list' ? 'sm:flex sm:flex-row' : ''"
                >
                    <a href="{{ route('profile.show', array_merge(['slug' => $profile['slug']], request()->query())) }}" class="absolute inset-0 z-10" aria-label="View profile for {{ $profile['name'] }}"></a>

                    {{-- Image --}}
                    <div class="view-card-media relative overflow-hidden" :class="viewMode === 'list' ? 'sm:w-44 sm:shrink-0' : 'rounded-t-2xl'">
                        @if($profile['image'])
                            <img
                                src="{{ $profile['image'] }}"
                                alt="{{ $profile['name'] }}"
                                class="view-card-image w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                :class="viewMode === 'list' ? 'h-48 sm:h-full' : 'h-52'"
                                loading="lazy"
                                decoding="async"
                                fetchpriority="low"
                            >
                        @else
                            <div class="flex items-center justify-center bg-gray-100 text-gray-400" :class="viewMode === 'list' ? 'h-48 sm:h-full' : 'h-52'">
                                <i class="fa-solid fa-image text-4xl"></i>
                            </div>
                        @endif

                        {{-- Photo Verified / Online badges --}}
                        <div class="absolute left-0 top-3 z-10 flex flex-col gap-1">
                            @if($profile['verified'])
                                <span class="inline-flex items-center gap-1 bg-cyan-500 px-2.5 py-1 text-[11px] font-semibold text-white shadow-sm" style="border-radius: 0 4px 4px 0;">
                                    <i class="fa-solid fa-camera text-[9px]"></i> Photo Verified
                                </span>
                            @endif
                            @if($profile['active'])
                                <span class="inline-flex items-center gap-1 bg-emerald-500 px-2.5 py-1 text-[11px] font-semibold text-white shadow-sm" style="border-radius: 0 4px 4px 0;">
                                    <span class="h-1.5 w-1.5 rounded-full bg-white animate-pulse"></span> Online Now
                                </span>
                            @endif

                        </div>
                    </div>

                    {{-- Content --}}
                    <div :class="viewMode === 'list' ? 'flex flex-col justify-between flex-1 p-4' : 'p-3.5'">
                        {{-- Date + Actions row --}}
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-[11px] text-gray-400">{{ $profile['date'] }}</span>
                            <div class="flex items-center gap-2 text-gray-400 relative z-20">
                                <button
                                    type="button"
                                    @click.prevent="toggleFavourite('{{ $profile['slug'] }}')"
                                    :class="isFavourite('{{ $profile['slug'] }}') ? 'text-pink-500' : 'hover:text-pink-500'"
                                    class="transition-colors"
                                    title="Favourite"
                                >
                                    <i :class="isFavourite('{{ $profile['slug'] }}') ? 'fa-solid fa-heart' : 'fa-regular fa-heart'" class="text-xs"></i>
                                </button>
                                <button
                                    type="button"
                                    @click.prevent="toggleBookmark('{{ $profile['slug'] }}')"
                                    :class="isBookmark('{{ $profile['slug'] }}') ? 'text-blue-500' : 'hover:text-blue-500'"
                                    class="transition-colors"
                                    title="Bookmark"
                                >
                                    <i :class="isBookmark('{{ $profile['slug'] }}') ? 'fa-solid fa-bookmark' : 'fa-regular fa-bookmark'" class="text-xs"></i>
                                </button>

                            </div>
                        </div>

                        {{-- Name --}}
                        <h3 class="text-sm font-medium text-gray-800 truncate" :class="viewMode === 'list' ? 'md:text-base' : ''">
                            {{ $profile['name'] }}@if($profile['suburb']) <span class="text-gray-400 font-normal">({{ $profile['suburb'] }})</span>@endif
                        </h3>

                        {{-- Rate --}}
                        <p class="mt-0.5 text-2xl font-bold text-gray-900" :class="viewMode === 'list' ? 'md:text-3xl' : ''">
                            {{ $profile['rate'] }}
                        </p>

                        {{-- Location + Service --}}
                        <div class="mt-3 flex flex-wrap items-start gap-x-4 gap-y-1.5 text-[12px] text-gray-600">
                            @if($profile['city'] || $profile['suburb'])
                                <span class="inline-flex items-center gap-1">
                                    <i class="fa-solid fa-location-dot text-pink-500 text-[11px]"></i>
                                    {{ $profile['suburb'] }}
                                </span>
                            @endif
                            @if(!empty($profile['service_1']))
                                <span class="inline-flex items-center gap-1">
                                    <i class="fa-solid fa-briefcase text-gray-400 text-[11px]"></i>
                                    {{ $profile['service_1'] }}
                                </span>
                            @endif
                        </div>

                        {{-- Categories --}}
                        @if(!empty($profile['service_2']) || !empty($profile['description']))
                            <div class="mt-2 text-[12px] text-gray-600 line-clamp-2">
                                <i class="fa-solid fa-gem text-blue-500 text-[10px] mr-1"></i>
                                {{ !empty($profile['service_2']) ? $profile['service_2'] : $profile['description'] }}
                            </div>
                        @endif
                    </div>
                </article>
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
    </div>
</div>

<button
    id="smooth-scroll-top"
    type="button"
    class="pointer-events-none fixed bottom-6 right-6 z-40 inline-flex h-11 w-11 items-center justify-center rounded-full bg-pink-600 text-white opacity-0 shadow-lg transition-all duration-300 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-400 focus:ring-offset-2"
    aria-label="Scroll to top"
>
    <i class="fa-solid fa-arrow-up text-sm"></i>
</button>
@endsection

@push('styles')
<style>
    html {
        scroll-behavior: smooth;
    }

    @media (prefers-reduced-motion: reduce) {
        html {
            scroll-behavior: auto;
        }
    }

    .view-card {
        transition: box-shadow 0.25s ease, border-color 0.25s ease, transform 0.2s ease;
    }

    .view-card:hover {
        transform: translateY(-2px);
    }

    .view-card-image {
        transform-origin: center center;
    }

    /* Pagination light theme */
    nav[aria-label="Pagination"] span,
    nav[aria-label="Pagination"] a {
        background-color: #ffffff !important;
        border-color: #d1d5db !important;
        color: #374151 !important;
    }

    nav[aria-label="Pagination"] a:hover {
        background-color: #f9fafb !important;
        color: #111827 !important;
    }

    nav[aria-label="Pagination"] [aria-current="page"] span,
    nav[aria-label="Pagination"] span[aria-current="page"] {
        background-color: #db2777 !important;
        border-color: #db2777 !important;
        color: #fff !important;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const scrollTopButton = document.getElementById('smooth-scroll-top');
        if (!scrollTopButton) {
            return;
        }

        const toggleScrollTopButton = function () {
            const shouldShow = window.scrollY > 300;
            scrollTopButton.classList.toggle('opacity-0', !shouldShow);
            scrollTopButton.classList.toggle('pointer-events-none', !shouldShow);
        };

        window.addEventListener('scroll', toggleScrollTopButton, { passive: true });

        scrollTopButton.addEventListener('click', function () {
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            window.scrollTo({ top: 0, behavior: prefersReducedMotion ? 'auto' : 'smooth' });
        });

        toggleScrollTopButton();
    });

    function escortSearch(config) {
        return {
            searchMode: config.initialMode || 'suburb',
            term: config.initialTerm || '',
            suggestions: [],
            showSuggestions: false,
            highlightedIndex: -1,
            abortController: null,

            fetchSuggestions() {
                const q = this.term.trim();
                if (q.length < 2) {
                    this.closeSuggestions();
                    return;
                }

                if (this.abortController) {
                    this.abortController.abort();
                }
                this.abortController = new AbortController();

                const isSuburbMode = this.searchMode === 'suburb';
                const url = (isSuburbMode ? config.suburbSuggestionsUrl : config.suggestionsUrl)
                    + '?q=' + encodeURIComponent(q);

                fetch(url, {
                    signal: this.abortController.signal,
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                })
                .then(r => r.ok ? r.json() : Promise.resolve(isSuburbMode ? [] : { suggestions: [] }))
                .then(data => {
                    if (isSuburbMode) {
                        this.suggestions = (Array.isArray(data) ? data : []).map(item => ({
                            type: 'suburb',
                            name: (item.suburb || '') + ', ' + (item.state || ''),
                            label: item.postcode || '',
                            value: item.suburb || '',
                        }));
                    } else {
                        this.suggestions = (data.suggestions || []).map(item => ({
                            type: 'profile',
                            name: item.name || '',
                            slug: item.slug || '',
                            label: item.location || '',
                            age: item.age,
                        }));
                    }
                    this.showSuggestions = this.suggestions.length > 0;
                    this.highlightedIndex = -1;
                })
                .catch(err => {
                    if (err.name !== 'AbortError') {
                        this.closeSuggestions();
                    }
                });
            },

            selectSuggestion(item, event) {
                if (item.type === 'suburb') {
                    this.term = item.value;
                    this.closeSuggestions();
                    const form = event.target.closest('form');
                    if (form) form.submit();
                } else {
                    window.location.href = '/profile/' + item.slug;
                }
            },

            closeSuggestions() {
                this.showSuggestions = false;
                this.highlightedIndex = -1;
            },

            highlightNext() {
                if (!this.showSuggestions) return;
                this.highlightedIndex = Math.min(this.highlightedIndex + 1, this.suggestions.length - 1);
            },

            highlightPrev() {
                if (!this.showSuggestions) return;
                this.highlightedIndex = Math.max(this.highlightedIndex - 1, -1);
            },

            selectHighlighted(event) {
                if (this.highlightedIndex >= 0 && this.suggestions[this.highlightedIndex]) {
                    this.selectSuggestion(this.suggestions[this.highlightedIndex], event);
                    return;
                }
                // Default: submit the form
                event.target.closest('form').submit();
            },
        };
    }

    function favouriteBookmark(config) {
        return {
            viewMode: 'grid',
            favourites: config.favourites || [],
            bookmarks: config.bookmarks || [],

            isFavourite(slug) {
                return this.favourites.includes(slug);
            },

            isBookmark(slug) {
                return this.bookmarks.includes(slug);
            },

            async toggleFavourite(slug) {
                const res = await fetch('/favourite/' + encodeURIComponent(slug), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                if (!res.ok) return;
                const data = await res.json();
                if (data.active) {
                    if (!this.favourites.includes(slug)) this.favourites.push(slug);
                } else {
                    this.favourites = this.favourites.filter(s => s !== slug);
                }
            },

            async toggleBookmark(slug) {
                const res = await fetch('/bookmark/' + encodeURIComponent(slug), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                if (!res.ok) return;
                const data = await res.json();
                if (data.active) {
                    if (!this.bookmarks.includes(slug)) this.bookmarks.push(slug);
                } else {
                    this.bookmarks = this.bookmarks.filter(s => s !== slug);
                }
            },
        };
    }
</script>
@endpush
