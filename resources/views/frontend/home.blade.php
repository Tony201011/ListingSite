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
                    distance: {{ $distanceFilter }},
                    maxDistance: {{ $maxSearchDistance }},
                    locationEnabled: {{ ($userLat !== null && $userLng !== null) ? 'true' : 'false' }}
                })" @keydown.escape="closeSuggestions()" @click.outside="closeSuggestions()">
                <form method="GET" action="{{ url('/') }}" @submit="handleFormSubmit($event)">
                    <input type="hidden" name="location" :value="searchMode === 'suburb' ? term : ''">
                    <input type="hidden" name="escort_name" :value="searchMode === 'username' ? term : ''">
                    <input type="hidden" name="user_lat" :value="userLat">
                    <input type="hidden" name="user_lng" :value="userLng">
                    <input type="hidden" name="distance" :value="locationEnabled ? distance : ''">

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        {{-- Text input --}}
                        <div class="relative flex-1">
                            <input
                                type="text"
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

                        {{-- Find Escorts button --}}
                        <button type="submit" class="shrink-0 rounded bg-fuchsia-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-fuchsia-700 active:scale-95">
                            Find Escorts
                        </button>

                        {{-- Advanced search link --}}
                        <a href="{{ route('advanced-search') }}" class="shrink-0 text-sm font-medium text-fuchsia-400 transition hover:text-fuchsia-300">
                            Advanced search
                        </a>

                        {{-- Search by Name / Search by Location toggle --}}
                        <button
                            type="button"
                            @click="searchMode = searchMode === 'suburb' ? 'username' : 'suburb'; term = ''; closeSuggestions()"
                            class="shrink-0 rounded bg-gray-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-gray-600"
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

        {{-- Profile Cards Grid --}}
        <div x-cloak class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
            @forelse($profiles as $profile)
                <article
                    class="group relative overflow-hidden rounded-2xl bg-white shadow-sm border border-gray-200 transition-all duration-300 hover:shadow-md hover:border-gray-300 hover:-translate-y-0.5"
                >
                    <a href="{{ route('profile.show', array_merge(['slug' => $profile['slug']], request()->query())) }}" class="absolute inset-0 z-10" aria-label="View profile for {{ $profile['name'] }}"></a>

                    {{-- Image --}}
                    <div class="relative overflow-hidden rounded-t-2xl">
                        @if($profile['image'])
                            <img
                                src="{{ $profile['image'] }}"
                                alt="{{ $profile['name'] }}"
                                class="w-full object-cover origin-center transition-transform duration-500 group-hover:scale-105 h-52"
                                loading="lazy"
                                decoding="async"
                                fetchpriority="low"
                            >
                        @else
                            <div class="flex items-center justify-center bg-gray-100 text-gray-400 h-52">
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
                    <div class="p-3.5">
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
                                @if($profile['age'])
                                    <span class="inline-flex items-center justify-center h-4 w-4 rounded bg-blue-600 text-white text-[9px] font-bold leading-none" aria-label="Age: {{ $profile['age'] }}">{{ $profile['age'] }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Name --}}
                        <h3 class="text-sm font-medium text-gray-800 truncate">
                            {{ $profile['name'] }}@if($profile['suburb']) <span class="text-gray-400 font-normal">({{ $profile['suburb'] }})</span>@endif
                        </h3>

                        {{-- Rate --}}
                        <p class="mt-0.5 text-2xl font-bold text-gray-900">
                            {{ $profile['rate'] }}
                        </p>

                        {{-- In Call / Out Call --}}
                        @if(!empty($profile['in_call']) || !empty($profile['out_call']))
                            <div class="mt-1.5 flex flex-wrap gap-x-3 gap-y-1 text-[11px]">
                                @if(!empty($profile['in_call']))
                                    <span class="inline-flex items-center gap-1 text-gray-600">
                                        <i class="fa-solid fa-house text-emerald-500 text-[10px]" aria-hidden="true"></i>
                                        <span class="font-medium">In:</span> {{ $profile['in_call'] }}
                                    </span>
                                @endif
                                @if(!empty($profile['out_call']))
                                    <span class="inline-flex items-center gap-1 text-gray-600">
                                        <i class="fa-solid fa-car text-blue-500 text-[10px]" aria-hidden="true"></i>
                                        <span class="font-medium">Out:</span> {{ $profile['out_call'] }}
                                    </span>
                                @endif
                            </div>
                        @endif

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

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/css/home.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('frontend/js/home.js') }}"></script>
@endpush
