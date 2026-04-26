@extends('layouts.frontend')

@section('title', 'Advanced Search / Filter')

@php
    $locationQuery = (string) ($locationQuery ?? '');
    $hasAgeFilter = $hasAgeFilter ?? false;
    $hasPriceFilter = $hasPriceFilter ?? false;
    $hasDistanceFilter = $hasDistanceFilter ?? false;
    $distanceSearchEnabled = $distanceSearchEnabled ?? true;
    $maxSearchDistance = (int) ($maxSearchDistance ?? 500);
    $distanceFilter = max(0, (int) ($distanceFilter ?? $maxSearchDistance));
    $userLat = $userLat ?? null;
    $userLng = $userLng ?? null;
    $girlsMode = (string) ($girlsMode ?? 'all');
    $selectedCategoryItems = $selectedCategoryItems ?? collect();
    $hasActiveFilters = $locationQuery !== '' || collect($selectedCategoryItems)->isNotEmpty() || $hasAgeFilter || $hasPriceFilter || $hasDistanceFilter;
@endphp

@section('content')
<div class="min-h-screen bg-gray-100 text-gray-800">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-4 flex items-center gap-2 text-xs text-gray-500">
            <a href="{{ url('/') }}" class="hover:text-gray-700">Home</a>
            <span>›</span>
            <span class="text-gray-700">Advanced Search / Filter</span>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
            <h1 class="text-xl font-bold text-gray-900">Advanced Search / Filter</h1>
            <p class="mt-1 text-sm text-gray-500">Use filters below to find matching profiles.</p>

            @php
                $selectedCategoryIds = collect($selectedCategoryIds ?? [])->map(fn ($id) => (string) $id)->values();
            @endphp

            <form method="GET" action="{{ route('advanced-search') }}" class="mt-6 space-y-4 text-sm text-gray-600" x-data="{
                minAge: {{ (int) ($minAge ?? 18) }},
                maxAge: {{ (int) ($maxAge ?? 60) }},
                ageMinLimit: 18,
                ageMaxLimit: 100,

                minPrice: {{ (int) ($minPrice ?? 0) }},
                maxPrice: {{ (int) ($maxPrice ?? 1000) }},
                priceMinLimit: 0,
                priceMaxLimit: 1000,

                distance: {{ $distanceFilter }},
                maxDistance: {{ $maxSearchDistance }},

                labelStyle(percent) {
                    const safePercent = Math.min(100, Math.max(0, percent));
                    return `left: ${safePercent}%; transform: translateX(-50%);`;
                },

                getDistancePercent() {
                    if (this.maxDistance <= 0) return 0;
                    return (this.distance / this.maxDistance) * 100;
                },

                getAgeMinPercent() {
                    return ((this.minAge - this.ageMinLimit) / (this.ageMaxLimit - this.ageMinLimit)) * 100;
                },
                getAgeMaxPercent() {
                    return ((this.maxAge - this.ageMinLimit) / (this.ageMaxLimit - this.ageMinLimit)) * 100;
                },
                setMinAge(value) {
                    const parsed = parseInt(value);
                    this.minAge = parsed > this.maxAge ? this.maxAge : parsed;
                },
                setMaxAge(value) {
                    const parsed = parseInt(value);
                    this.maxAge = parsed < this.minAge ? this.minAge : parsed;
                },

                getPriceMinPercent() {
                    return ((this.minPrice - this.priceMinLimit) / (this.priceMaxLimit - this.priceMinLimit)) * 100;
                },
                getPriceMaxPercent() {
                    return ((this.maxPrice - this.priceMinLimit) / (this.priceMaxLimit - this.priceMinLimit)) * 100;
                },
                setMinPrice(value) {
                    const parsed = parseInt(value);
                    this.minPrice = parsed > this.maxPrice ? this.maxPrice : parsed;
                },
                setMaxPrice(value) {
                    const parsed = parseInt(value);
                    this.maxPrice = parsed < this.minPrice ? this.minPrice : parsed;
                }
            }">
                <div
                    x-data="{
                        term: {!! \Illuminate\Support\Js::from(request('location', '')) !!},
                        selectedState: {!! \Illuminate\Support\Js::from(request('location_state', '')) !!},
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

                            fetch('{{ route('api.suburbs.search') }}?q=' + encodeURIComponent(q), {
                                signal: this.abortController.signal,
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(r => r.ok ? r.json() : Promise.resolve([]))
                            .then(data => {
                                this.suggestions = (Array.isArray(data) ? data : []).map(item => ({
                                    name: ((item.suburb || '') + ', ' + (item.state || '')).replace(/^, |, $/g, ''),
                                    value: item.suburb || '',
                                    state: item.state || '',
                                    label: item.postcode || ''
                                }));

                                this.showSuggestions = this.suggestions.length > 0;
                                this.highlightedIndex = -1;
                            })
                            .catch(err => {
                                if (err.name !== 'AbortError') {
                                    this.closeSuggestions();
                                }
                            });
                        },

                        selectSuggestion(item) {
                            this.term = item.value;
                            this.selectedState = item.state || '';
                            this.closeSuggestions();
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

                        selectHighlighted() {
                            if (this.highlightedIndex >= 0 && this.suggestions[this.highlightedIndex]) {
                                this.selectSuggestion(this.suggestions[this.highlightedIndex]);
                            } else {
                                this.closeSuggestions();
                            }
                        }
                    }"
                    @keydown.escape="closeSuggestions()"
                    @click.outside="closeSuggestions()"
                    class="space-y-4"
                >
                    <div class="relative">
                        <label for="location" class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-700">Location</label>

                        <input
                            id="location"
                            name="location"
                            type="text"
                            x-model="term"
                            @input.debounce.300ms="selectedState = ''; fetchSuggestions()"
                            @focus="fetchSuggestions()"
                            @keydown.arrow-down.prevent="highlightNext()"
                            @keydown.arrow-up.prevent="highlightPrev()"
                            @keydown.enter.prevent="selectHighlighted()"
                            placeholder="Type location & select result from list"
                            class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 placeholder:text-gray-400 focus:border-pink-400 focus:outline-none"
                            autocomplete="off"
                        >

                        <input type="hidden" name="location_state" x-model="selectedState">

                        <div
                            x-show="showSuggestions && suggestions.length > 0"
                            x-cloak
                            x-transition
                            class="absolute left-0 right-0 top-full z-[9999] mt-1 max-h-64 overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-xl"
                        >
                            <ul class="divide-y divide-gray-100">
                                <template x-for="(item, index) in suggestions" :key="index">
                                    <li>
                                        <button
                                            type="button"
                                            @mousedown.prevent="selectSuggestion(item)"
                                            class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm transition"
                                            :class="index === highlightedIndex ? 'bg-pink-50 text-pink-700' : 'text-gray-700 hover:bg-gray-50'"
                                            @mouseenter="highlightedIndex = index"
                                            @mouseleave="highlightedIndex = -1"
                                        >
                                            <i class="fa-solid fa-location-dot shrink-0 text-xs text-gray-400"></i>
                                            <span class="truncate" x-text="item.name"></span>
                                            <span class="ml-auto shrink-0 text-xs text-gray-400" x-show="item.label" x-text="item.label"></span>
                                        </button>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white px-3 py-5">
                    <label for="distance" class="mb-4 block text-xs font-bold uppercase tracking-wide text-gray-700">Distance</label>

                    <div class="relative h-14">
                        <div class="absolute left-0 right-0 top-8 h-2 -translate-y-1/2 rounded-full bg-gray-200"></div>

                        <div
                            class="absolute left-0 top-8 h-2 -translate-y-1/2 rounded-full bg-pink-500"
                            :style="`width: ${getDistancePercent()}%`"
                        ></div>

                        <div
                            class="slider-value-label absolute top-0 rounded bg-pink-500 px-2 py-0.5 text-[11px] font-semibold text-white shadow"
                            :style="labelStyle(getDistancePercent())"
                        >
                            <span x-text="distance"></span> km
                        </div>

                        <input
                            id="distance"
                            name="distance"
                            type="range"
                            min="0"
                            max="{{ $maxSearchDistance }}"
                            step="1"
                            x-model.number="distance"
                            class="range-thumb absolute left-0 top-8 z-20 h-2 w-full -translate-y-1/2 appearance-none bg-transparent"
                        >
                    </div>

                    <div class="mt-2 flex items-center justify-between text-[11px] text-gray-400">
                        <span>0 km</span>
                        <span>{{ $maxSearchDistance }} km</span>
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-700">Age Range</label>
                    <div class="rounded-lg border border-gray-200 bg-white px-3 py-5">
                        <div class="relative h-14">
                            <div class="absolute left-0 right-0 top-8 h-2 -translate-y-1/2 rounded-full bg-gray-200"></div>

                            <div
                                class="absolute top-8 h-2 -translate-y-1/2 rounded-full bg-pink-500"
                                :style="`left: ${getAgeMinPercent()}%; right: ${100 - getAgeMaxPercent()}%`"
                            ></div>

                            <div
                                class="slider-value-label absolute top-0 rounded bg-pink-500 px-2 py-0.5 text-[11px] font-semibold text-white shadow"
                                :style="labelStyle(getAgeMinPercent())"
                            >
                                <span x-text="minAge"></span>
                            </div>

                            <div
                                class="slider-value-label absolute top-0 rounded bg-pink-500 px-2 py-0.5 text-[11px] font-semibold text-white shadow"
                                :style="labelStyle(getAgeMaxPercent())"
                            >
                                <span x-text="maxAge"></span>
                            </div>

                            <input
                                id="min-age"
                                name="min_age"
                                type="range"
                                min="18"
                                max="100"
                                step="1"
                                x-model.number="minAge"
                                @input="setMinAge($event.target.value)"
                                class="range-thumb pointer-events-none absolute left-0 top-8 z-20 h-2 w-full -translate-y-1/2 appearance-none bg-transparent"
                            >

                            <input
                                id="max-age"
                                name="max_age"
                                type="range"
                                min="18"
                                max="100"
                                step="1"
                                x-model.number="maxAge"
                                @input="setMaxAge($event.target.value)"
                                class="range-thumb pointer-events-none absolute left-0 top-8 z-20 h-2 w-full -translate-y-1/2 appearance-none bg-transparent"
                            >
                        </div>

                        <div class="mt-2 flex items-center justify-between text-[11px] text-gray-400">
                            <span>18</span>
                            <span>100</span>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-700">Price Range</label>
                    <div class="rounded-lg border border-gray-200 bg-white px-3 py-5">
                        <div class="relative h-14">
                            <div class="absolute left-0 right-0 top-8 h-2 -translate-y-1/2 rounded-full bg-gray-200"></div>

                            <div
                                class="absolute top-8 h-2 -translate-y-1/2 rounded-full bg-pink-500"
                                :style="`left: ${getPriceMinPercent()}%; right: ${100 - getPriceMaxPercent()}%`"
                            ></div>

                            <div
                                class="slider-value-label absolute top-0 rounded bg-pink-500 px-2 py-0.5 text-[11px] font-semibold text-white shadow"
                                :style="labelStyle(getPriceMinPercent())"
                            >
                                $<span x-text="minPrice"></span>
                            </div>

                            <div
                                class="slider-value-label absolute top-0 rounded bg-pink-500 px-2 py-0.5 text-[11px] font-semibold text-white shadow"
                                :style="labelStyle(getPriceMaxPercent())"
                            >
                                $<span x-text="maxPrice"></span>
                            </div>

                            <input
                                id="min-price"
                                name="min_price"
                                type="range"
                                min="0"
                                max="1000"
                                step="10"
                                x-model.number="minPrice"
                                @input="setMinPrice($event.target.value)"
                                class="range-thumb pointer-events-none absolute left-0 top-8 z-20 h-2 w-full -translate-y-1/2 appearance-none bg-transparent"
                            >

                            <input
                                id="max-price"
                                name="max_price"
                                type="range"
                                min="0"
                                max="1000"
                                step="10"
                                x-model.number="maxPrice"
                                @input="setMaxPrice($event.target.value)"
                                class="range-thumb pointer-events-none absolute left-0 top-8 z-20 h-2 w-full -translate-y-1/2 appearance-none bg-transparent"
                            >
                        </div>

                        <div class="mt-2 flex items-center justify-between text-[11px] text-gray-400">
                            <span>$0</span>
                            <span>$1000</span>
                        </div>
                    </div>
                </div>

                @forelse(($filterGroups ?? []) as $group)
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-700">{{ $group['label'] }}</label>

                        @if(!empty($group['options']))
                            @php
                                $groupSelectedIds = collect($group['options'])
                                    ->pluck('id')
                                    ->map(fn ($id) => (string) $id)
                                    ->intersect($selectedCategoryIds)
                                    ->values()
                                    ->all();
                            @endphp

                            <div
                                class="space-y-2"
                                x-data="{
                                    open: false,
                                    options: {{ \Illuminate\Support\Js::from($group['options']) }},
                                    selected: {{ \Illuminate\Support\Js::from($groupSelectedIds) }}
                                }"
                                @click.outside="open = false"
                                @filter-opened.window="if ($event.detail !== '{{ $group['slug'] }}') open = false"
                            >
                                <template x-for="id in selected" :key="'sel-' + id">
                                    <input type="hidden" name="categories[]" :value="id">
                                </template>

                                <button
                                    type="button"
                                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-left text-sm font-semibold text-gray-700"
                                    @click="open = !open; if (open) $dispatch('filter-opened', '{{ $group['slug'] }}')"
                                >
                                    <span class="text-gray-500" x-show="selected.length === 0">Please select...</span>

                                    <span class="inline-flex flex-wrap items-center gap-1" x-show="selected.length > 0">
                                        <template x-for="id in selected" :key="'chip-' + id">
                                            <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">
                                                <span x-text="(options.find(o => String(o.id) === String(id)) || {}).name"></span>
                                                <button type="button" class="text-gray-500" @click.stop="selected = selected.filter(v => String(v) !== String(id))">×</button>
                                            </span>
                                        </template>
                                    </span>
                                </button>

                                <div x-cloak x-show="open" x-transition class="max-h-40 space-y-2 overflow-y-auto rounded-lg border border-gray-200 bg-white p-3">
                                    <template x-for="option in options.filter(o => !selected.includes(String(o.id)))" :key="'opt-' + option.id">
                                        <button
                                            type="button"
                                            class="block w-full rounded px-2 py-1 text-left text-sm text-gray-700 hover:bg-gray-100"
                                            @click="selected = [...selected, String(option.id)]"
                                            x-text="option.name"
                                        ></button>
                                    </template>

                                    <p class="text-xs text-gray-400" x-show="options.filter(o => !selected.includes(String(o.id))).length === 0">
                                        No options left.
                                    </p>
                                </div>
                            </div>
                        @else
                            <span class="text-xs text-gray-400">No options.</span>
                        @endif
                    </div>
                @empty
                    <span class="text-xs text-gray-400">No filters found.</span>
                @endforelse

                <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center">
                    <button type="submit" class="inline-flex items-center justify-center rounded-md bg-[#b58aac] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#a6749b] sm:min-w-[200px]">
                        Apply Filters
                    </button>

                    <a href="{{ route('advanced-search') }}" class="inline-flex items-center justify-center rounded-md bg-[#b58aac] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#a6749b] sm:min-w-[200px]">
                        Reset
                    </a>
                </div>
            </form>
        </div>

                <div class="mt-6"
            x-data="favouriteBookmark({
                viewMode: 'grid',
                favourites: {{ Js::from($userFavourites ?? []) }},
                bookmarks: {{ Js::from($userBookmarks ?? []) }}
            })"
        >
            <div class="mb-5 flex flex-wrap items-center gap-3 border-b border-gray-200 pb-4">
                @php
                    $currentQuery = request()->query();
                    $girlsUrl = fn (string $mode): string => route('advanced-search', array_merge($currentQuery, ['girls' => $mode]));
                @endphp
                <div class="flex items-center gap-2">
                    <a
                        href="{{ $girlsUrl('new') }}"
                        class="rounded-full border px-4 py-1.5 text-xs font-semibold transition {{ $girlsMode === 'new' ? 'border-pink-600 bg-pink-600/10 text-pink-600' : 'border-gray-300 bg-white text-gray-600 hover:border-pink-300 hover:text-pink-600' }}"
                    >
                        New girls
                    </a>
                    <a
                        href="{{ $girlsUrl('all') }}"
                        class="rounded-full border px-4 py-1.5 text-xs font-semibold transition {{ $girlsMode === 'all' ? 'border-pink-600 bg-pink-600/10 text-pink-600' : 'border-gray-300 bg-white text-gray-600 hover:border-pink-300 hover:text-pink-600' }}"
                    >
                        All girls
                    </a>
                    <a
                        href="{{ $girlsUrl('popular') }}"
                        class="inline-flex items-center gap-1 rounded-full border px-4 py-1.5 text-xs font-semibold transition {{ $girlsMode === 'popular' ? 'border-pink-600 bg-pink-600/10 text-pink-600' : 'border-gray-300 bg-white text-gray-600 hover:border-pink-300 hover:text-pink-600' }}"
                    >
                        <i class="fa-solid fa-fire text-[10px]"></i>
                        Popular
                    </a>
                </div>
            </div>

            @if($hasActiveFilters)
                <div class="mb-4 flex flex-wrap gap-2">
                    @if($locationQuery !== '')
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white border border-gray-300 px-3 py-1 text-xs text-gray-700">
                            <i class="fa-solid fa-location-dot text-pink-500 text-[10px]"></i> {{ $locationQuery }}
                        </span>
                    @endif
                    @foreach(collect($selectedCategoryItems) as $item)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white border border-gray-300 px-3 py-1 text-xs text-gray-700">
                            {{ $item['name'] }}
                        </span>
                    @endforeach
                    @if($hasAgeFilter)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white border border-gray-300 px-3 py-1 text-xs text-gray-700">
                            Age: {{ $minAge }}–{{ $maxAge }}
                        </span>
                    @endif
                    @if($hasPriceFilter)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white border border-gray-300 px-3 py-1 text-xs text-gray-700">
                            Price: ${{ $minPrice }}–${{ $maxPrice }}
                        </span>
                    @endif
                    @if($hasDistanceFilter)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white border border-gray-300 px-3 py-1 text-xs text-gray-700">
                            <i class="fa-solid fa-location-crosshairs text-pink-500 text-[10px]"></i> Within {{ $distanceFilter }} km
                        </span>
                    @endif
                </div>
            @endif

            <div x-cloak>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @forelse($profiles as $profile)
                        <article
                            class="view-card group relative overflow-hidden rounded-2xl bg-white shadow-sm border border-gray-200 transition-all duration-300 hover:shadow-md hover:border-gray-300"
                        >
                            <a href="{{ route('profile.show', array_merge(['slug' => $profile['slug']], request()->query())) }}" class="absolute inset-0 z-10" aria-label="View profile for {{ $profile['name'] }}"></a>

                            <div class="view-card-media relative overflow-hidden rounded-t-2xl">
                                @if($profile['image'])
                                    <img
                                        src="{{ $profile['image'] }}"
                                        alt="{{ $profile['name'] }}"
                                        class="view-card-image w-full object-cover transition-transform duration-500 group-hover:scale-105 h-52"
                                        loading="lazy"
                                        decoding="async"
                                        fetchpriority="low"
                                    >
                                @else
                                    <div class="flex items-center justify-center bg-gray-100 text-gray-400 h-52">
                                        <i class="fa-solid fa-image text-4xl"></i>
                                    </div>
                                @endif

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

                            <div class="p-3.5">
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
                                        @if($profile['age'])
                                            <span class="inline-flex items-center justify-center h-4 w-4 rounded bg-blue-600 text-white text-[9px] font-bold leading-none">{{ $profile['age'] }}</span>
                                        @endif
                                    </div>
                                </div>

                                <h3 class="text-sm font-medium text-gray-800 truncate">
                                    {{ $profile['name'] }}@if($profile['suburb']) <span class="text-gray-400 font-normal">({{ $profile['suburb'] }})</span>@endif
                                </h3>

                                <p class="mt-0.5 text-2xl font-bold text-gray-900">
                                    {{ $profile['rate'] }}
                                </p>

                                <div class="mt-3 flex flex-wrap items-start gap-x-4 gap-y-1.5 text-[12px] text-gray-600">
                                    @if($profile['city'] || $profile['suburb'])
                                        <span class="inline-flex items-center gap-1">
                                            <i class="fa-solid fa-location-dot text-pink-500 text-[11px]"></i>
                                            {{ $profile['suburb'] ?: $profile['city'] }}
                                            @if(isset($profile['distance_km']) && $profile['distance_km'] !== null)
                                                <span class="text-gray-400">({{ $profile['distance_km'] }} km)</span>
                                            @endif
                                        </span>
                                    @endif
                                    @if(!empty($profile['service_1']))
                                        <span class="inline-flex items-center gap-1">
                                            <i class="fa-solid fa-briefcase text-gray-400 text-[11px]"></i>
                                            {{ $profile['service_1'] }}
                                        </span>
                                    @endif
                                </div>

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
                                <a href="{{ route('advanced-search') }}" class="mt-4 inline-block rounded-lg bg-pink-600 px-5 py-2 text-sm font-semibold text-white hover:bg-pink-700 transition">Clear filters</a>
                            @endif
                        </div>
                    @endforelse
                </div>

                <div class="mt-8">
                    @if($hasActiveFilters)
                        <p class="mb-3 text-center text-sm text-gray-500">
                            <a href="{{ route('advanced-search') }}" class="text-pink-500 hover:text-pink-400 underline underline-offset-2">Clear filters</a>
                        </p>
                    @endif
                    {{ $profiles->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/css/home.css') }}">

<style>
    [x-cloak] {
        display: none !important;
    }

    .range-thumb::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        pointer-events: auto;
        height: 18px;
        width: 18px;
        border-radius: 9999px;
        background: #ec4899;
        border: 2px solid #ffffff;
        box-shadow: 0 1px 4px rgba(0,0,0,0.2);
        cursor: pointer;
    }

    .range-thumb::-moz-range-thumb {
        pointer-events: auto;
        height: 18px;
        width: 18px;
        border-radius: 9999px;
        background: #ec4899;
        border: 2px solid #ffffff;
        box-shadow: 0 1px 4px rgba(0,0,0,0.2);
        cursor: pointer;
    }

    .range-thumb::-webkit-slider-runnable-track {
        background: transparent;
    }

    .range-thumb::-moz-range-track {
        background: transparent;
    }

    .slider-value-label {
        white-space: nowrap;
        max-width: 70px;
        text-align: center;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('frontend/js/home.js') }}"></script>
@endpush
