@extends('layouts.frontend')

@section('title', 'Advanced Search / Filter')

@php
    $locationQuery = (string) ($locationQuery ?? '');
    $escortNameQuery = (string) ($escortNameQuery ?? '');
    $hasAgeFilter = $hasAgeFilter ?? false;
    $hasPriceFilter = $hasPriceFilter ?? false;
    $hasDistanceFilter = $hasDistanceFilter ?? false;
    $maxSearchDistance = (int) ($maxSearchDistance ?? 500);
    $distanceFilter = (int) ($distanceFilter ?? $maxSearchDistance);
    $userLat = $userLat ?? null;
    $userLng = $userLng ?? null;
    $selectedCategoryItems = $selectedCategoryItems ?? collect();
    $hasActiveFilters = $locationQuery !== '' || $escortNameQuery !== '' || collect($selectedCategoryItems)->isNotEmpty() || $hasAgeFilter || $hasPriceFilter || $hasDistanceFilter;
@endphp

@section('content')
<div class="min-h-screen bg-gray-50 text-gray-800">
    <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
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
                maxAge: {{ (int) ($maxAge ?? 40) }},
                minPrice: {{ (int) ($minPrice ?? 150) }},
                maxPrice: {{ (int) ($maxPrice ?? 400) }},
                distance: {{ $distanceFilter }},
                maxDistance: {{ $maxSearchDistance }},
                userLat: '{{ $userLat ?? '' }}',
                userLng: '{{ $userLng ?? '' }}',
                locationEnabled: {{ ($userLat !== null && $userLng !== null) ? 'true' : 'false' }},
                geoError: '',
                requestLocation() {
                    this.geoError = '';
                    if (!navigator.geolocation) {
                        this.geoError = 'Geolocation is not supported by your browser.';
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
                                this.geoError = 'Unable to retrieve your location. Please allow location access.';
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
            }">
                <div>
                    <label for="location" class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-700">Location</label>
                    <input
                        id="location"
                        name="location"
                        type="text"
                        value="{{ request('location') }}"
                        placeholder="Type location & select result from list"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 placeholder:text-gray-400 focus:border-pink-400 focus:outline-none"
                    >
                </div>

                <div>
                    <label for="escort_name" class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-700">Escort Name</label>
                    <input
                        id="escort_name"
                        name="escort_name"
                        type="text"
                        value="{{ request('escort_name') }}"
                        placeholder="Search by escort name…"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 placeholder:text-gray-400 focus:border-pink-400 focus:outline-none"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-700">Age Range</label>
                    <div class="rounded-lg border border-gray-200 bg-white px-3 py-3">
                        <div class="mb-2 flex items-center justify-between text-xs text-gray-500">
                            <span>Min: <strong x-text="minAge"></strong></span>
                            <span>Max: <strong x-text="maxAge"></strong></span>
                        </div>
                        <input id="min-age" name="min_age" type="range" min="18" max="60" x-model.number="minAge" @input="if (minAge > maxAge) maxAge = minAge" class="w-full">
                        <input id="max-age" name="max_age" type="range" min="18" max="60" x-model.number="maxAge" @input="if (maxAge < minAge) minAge = maxAge" class="mt-2 w-full">
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-700">Price Range</label>
                    <div class="rounded-lg border border-gray-200 bg-white px-3 py-3">
                        <div class="mb-2 flex items-center justify-between text-xs text-gray-500">
                            <span>Min: $<strong x-text="minPrice"></strong></span>
                            <span>Max: $<strong x-text="maxPrice"></strong></span>
                        </div>
                        <input id="min-price" name="min_price" type="range" min="100" max="1000" step="10" x-model.number="minPrice" @input="if (minPrice > maxPrice) maxPrice = minPrice" class="w-full">
                        <input id="max-price" name="max_price" type="range" min="100" max="1000" step="10" x-model.number="maxPrice" @input="if (maxPrice < minPrice) minPrice = maxPrice" class="mt-2 w-full">
                    </div>
                </div>

                {{-- Distance / Near Me filter --}}
                <input type="hidden" name="user_lat" :value="userLat">
                <input type="hidden" name="user_lng" :value="userLng">
                <input type="hidden" name="distance" :value="locationEnabled ? distance : ''">
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-700">Distance (Near Me)</label>
                    <div class="rounded-lg border border-gray-200 bg-white px-3 py-3">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <button
                                type="button"
                                x-show="!locationEnabled"
                                @click="requestLocation()"
                                class="inline-flex items-center gap-1.5 rounded-md bg-pink-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-pink-700"
                            >
                                <i class="fa-solid fa-location-crosshairs text-[10px]"></i> Use My Location
                            </button>
                            <button
                                type="button"
                                x-show="locationEnabled"
                                x-cloak
                                @click="clearLocation()"
                                class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:border-gray-400"
                            >
                                <i class="fa-solid fa-xmark text-[10px]"></i> Clear Location
                            </button>
                            <span x-show="locationEnabled" x-cloak class="text-xs text-green-600">
                                <i class="fa-solid fa-circle-check text-[10px]"></i> Location detected
                            </span>
                            <span x-show="geoError" x-cloak class="text-xs text-red-500" x-text="geoError"></span>
                        </div>
                        <div x-show="locationEnabled" x-cloak>
                            <div class="mb-1 flex items-center justify-between text-xs text-gray-500">
                                <span>Within: <strong x-text="distance"></strong> km</span>
                                <span class="text-gray-400">Max: {{ $maxSearchDistance }} km</span>
                            </div>
                            <input
                                type="range"
                                min="1"
                                :max="maxDistance"
                                step="10"
                                x-model.number="distance"
                                class="w-full"
                            >
                        </div>
                        <p x-show="!locationEnabled" class="text-xs text-gray-400">Click "Use My Location" to filter by distance.</p>
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
                                x-data="{ open: false, options: {{ \Illuminate\Support\Js::from($group['options']) }}, selected: {{ \Illuminate\Support\Js::from($groupSelectedIds) }} }"
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
                                        <button type="button" class="block w-full rounded px-2 py-1 text-left text-sm text-gray-700 hover:bg-gray-100" @click="selected = [...selected, String(option.id)]" x-text="option.name"></button>
                                    </template>
                                    <p class="text-xs text-gray-400" x-show="options.filter(o => !selected.includes(String(o.id))).length === 0">No options left.</p>
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
                    <button type="submit" class="inline-flex items-center justify-center rounded-md bg-[#b58aac] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#a6749b] sm:min-w-[200px]">Apply Filters</button>
                    <a href="{{ route('advanced-search') }}" class="inline-flex items-center justify-center rounded-md bg-[#b58aac] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#a6749b] sm:min-w-[200px]">Reset</a>
                </div>
            </form>
        </div>

        {{-- Results --}}
        <div class="mt-6"
            x-data="favouriteBookmark({
                favourites: {{ Js::from($userFavourites ?? []) }},
                bookmarks: {{ Js::from($userBookmarks ?? []) }}
            })"
        >
            <div class="mb-4 flex flex-wrap items-center gap-3">
                <div class="ml-auto flex items-center gap-2">
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

            {{-- Profile cards --}}
            <div x-cloak class="grid gap-4" :class="viewMode === 'grid' ? 'sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4' : 'grid-cols-1'">
                @forelse($profiles as $profile)
                    <article
                        class="view-card group relative overflow-hidden rounded-2xl bg-white shadow-sm border border-gray-200 transition-all duration-300 hover:shadow-md hover:border-gray-300"
                        :class="viewMode === 'list' ? 'md:flex md:flex-row' : ''"
                    >
                        <a href="{{ route('profile.show', array_merge(['slug' => $profile['slug']], request()->query())) }}" class="absolute inset-0 z-10" aria-label="View profile for {{ $profile['name'] }}"></a>

                        {{-- Image --}}
                        <div class="view-card-media relative overflow-hidden rounded-t-2xl" :class="viewMode === 'list' ? 'md:w-56 md:shrink-0 md:rounded-none md:rounded-l-2xl' : ''">
                            @if($profile['image'])
                                <img
                                    src="{{ $profile['image'] }}"
                                    alt="{{ $profile['name'] }}"
                                    class="view-card-image w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                    :class="viewMode === 'list' ? 'h-56 md:h-full' : 'h-52'"
                                    loading="lazy"
                                >
                            @else
                                <div class="flex items-center justify-center bg-gray-100 text-gray-400" :class="viewMode === 'list' ? 'h-56 md:h-full' : 'h-52'">
                                    <i class="fa-solid fa-image text-4xl"></i>
                                </div>
                            @endif

                            {{-- Badges --}}
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
                        <div class="p-3.5" :class="viewMode === 'list' ? 'flex flex-col justify-between flex-1 p-4' : ''">
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

                            <h3 class="text-sm font-medium text-gray-800 truncate" :class="viewMode === 'list' ? 'md:text-base' : ''">
                                {{ $profile['name'] }}@if($profile['suburb']) <span class="text-gray-400 font-normal">({{ $profile['suburb'] }})</span>@endif
                            </h3>

                            <p class="mt-0.5 text-2xl font-bold text-gray-900" :class="viewMode === 'list' ? 'md:text-3xl' : ''">
                                {{ $profile['rate'] }}
                            </p>

                            <div class="mt-3 flex flex-wrap items-start gap-x-4 gap-y-1.5 text-[12px] text-gray-600">
                                @if($profile['city'] || $profile['suburb'])
                                    <span class="inline-flex items-center gap-1">
                                        <i class="fa-solid fa-location-dot text-pink-500 text-[11px]"></i>
                                        {{ $profile['city'] ?: $profile['suburb'] }}
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

            {{-- Pagination --}}
            <div class="mt-8">
                @if($hasActiveFilters)
                    <p class="mb-3 text-center text-sm text-gray-500">
                        <a href="{{ route('advanced-search') }}" class="text-pink-500 hover:text-pink-400 underline underline-offset-2">Clear filters</a>
                    </p>
                @endif
                <div class="flex justify-center">
                    {{ $profiles->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .view-card {
        transition: box-shadow 0.25s ease, border-color 0.25s ease, transform 0.2s ease;
    }

    .view-card:hover {
        transform: translateY(-2px);
    }

    .view-card-image {
        transform-origin: center center;
    }

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
