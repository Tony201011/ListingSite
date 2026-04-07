@extends('layouts.frontend')

@section('title', 'Home')

@php
    $selectedCategoryIds = collect($selectedCategoryIds ?? []);
    $locationQuery = (string) ($locationQuery ?? '');
    $escortNameQuery = (string) ($escortNameQuery ?? '');
    $hasAgeFilter = $hasAgeFilter ?? false;
    $hasPriceFilter = $hasPriceFilter ?? false;
    $selectedCategoryItems = $selectedCategoryItems ?? collect();
    $hasActiveFilters = $locationQuery !== '' || $escortNameQuery !== '' || collect($selectedCategoryItems)->isNotEmpty() || $hasAgeFilter || $hasPriceFilter;
@endphp

@section('content')
<div class="min-h-screen bg-gray-50 text-gray-800">

    {{-- Hero / Search Section --}}
    <div class="relative overflow-hidden bg-gradient-to-b from-gray-950 via-gray-900 to-gray-900">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_rgba(236,72,153,0.15)_0%,_transparent_65%)] pointer-events-none"></div>
        <div class="relative mx-auto max-w-4xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8 text-center">
            <h1 class="mb-2 text-3xl font-extrabold tracking-tight text-white sm:text-4xl lg:text-5xl">
                Popular <span class="text-pink-500">Hot</span>escorts
            </h1>
            <p class="mb-8 text-sm text-gray-400 tracking-widest uppercase">100% Real &amp; Genuine Escorts · Australia-Wide</p>

            <div x-data="{ searchMode: '{{ $escortNameQuery !== '' ? 'username' : 'suburb' }}', term: '{{ e($escortNameQuery !== '' ? $escortNameQuery : $locationQuery) }}' }">
                <form method="GET" action="{{ url('/') }}">
                    <input type="hidden" name="location" :value="searchMode === 'suburb' ? term : ''">
                    <input type="hidden" name="escort_name" :value="searchMode === 'username' ? term : ''">

                    <div class="mx-auto flex max-w-2xl flex-col gap-3 sm:flex-row sm:items-center">
                        <div class="relative flex-1">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                            <input
                                type="text"
                                x-model="term"
                                :placeholder="searchMode === 'username' ? 'Search by escort name…' : 'Search by suburb or city…'"
                                class="w-full rounded-xl border border-gray-700 bg-gray-800/80 py-3 pl-10 pr-4 text-sm text-white placeholder:text-gray-500 focus:border-pink-500 focus:outline-none focus:ring-1 focus:ring-pink-500/50"
                            >
                        </div>
                        <button type="submit" class="shrink-0 rounded-xl bg-pink-600 px-7 py-3 text-sm font-semibold text-white shadow-lg shadow-pink-900/40 transition hover:bg-pink-700 active:scale-95">
                            Find Escort
                        </button>
                    </div>

                    <div class="mt-3 flex flex-wrap justify-center gap-2">
                        <button
                            type="button"
                            @click="searchMode = searchMode === 'suburb' ? 'username' : 'suburb'; term = ''"
                            class="rounded-lg border border-gray-700 bg-gray-800 px-4 py-2 text-xs font-medium text-gray-300 transition hover:border-pink-500 hover:text-pink-400"
                            x-text="searchMode === 'suburb' ? '🔎 Search by Name' : '📍 Search by Suburb'"
                        ></button>
                        <a href="{{ route('advanced-search') }}" class="rounded-lg border border-pink-700/50 bg-pink-600/10 px-4 py-2 text-xs font-medium text-pink-400 transition hover:bg-pink-600/20 hover:text-pink-300">
                            <i class="fa-solid fa-sliders mr-1"></i> Advanced Search / Filter
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8" x-data="{ viewMode: 'grid' }">

        {{-- Toolbar: filters, sort, view toggle --}}
        <div class="mb-5 flex flex-wrap items-center gap-3 border-b border-gray-200 pb-4">
            <div class="flex items-center gap-2">
                <span class="rounded-full border border-gray-300 bg-white px-4 py-1.5 text-xs font-semibold text-gray-700 cursor-default">New girls</span>
                <span class="rounded-full border border-pink-600 bg-pink-600/10 px-4 py-1.5 text-xs font-semibold text-pink-600">All girls</span>
            </div>

            <div class="flex items-center gap-1 text-xs text-gray-600 ml-1">
                <i class="fa-solid fa-fire text-pink-500 text-[10px]"></i>
                <span>Popular</span>
            </div>

            <div class="ml-auto flex items-center gap-2 text-xs text-gray-600">
                <span class="hidden sm:inline text-gray-500">
                    {{ $profiles->total() }} {{ $profiles->total() === 1 ? 'profile' : 'profiles' }}
                    @if($hasActiveFilters)
                        <a href="{{ url('/') }}" class="ml-2 text-pink-500 hover:text-pink-400 underline underline-offset-2">Clear filters</a>
                    @endif
                </span>
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
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 border border-gray-300 px-3 py-1 text-xs text-gray-700">
                        <i class="fa-solid fa-location-dot text-pink-500 text-[10px]"></i> {{ $locationQuery }}
                    </span>
                @endif
                @if($escortNameQuery !== '')
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 border border-gray-300 px-3 py-1 text-xs text-gray-700">
                        <i class="fa-solid fa-user text-pink-500 text-[10px]"></i> {{ $escortNameQuery }}
                    </span>
                @endif
                @foreach(collect($selectedCategoryItems) as $item)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 border border-gray-300 px-3 py-1 text-xs text-gray-700">
                            {{ $item['name'] }}
                        </span>
                @endforeach
            </div>
        @endif

        {{-- Profile Cards Grid / List --}}
        <div x-cloak class="grid gap-4" :class="viewMode === 'grid' ? 'sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5' : 'grid-cols-1'">
            @forelse($profiles as $profile)
                <article
                    class="view-card group relative overflow-hidden rounded-2xl border border-gray-800 bg-gray-850 shadow-md transition-all duration-300 hover:border-pink-700/50 hover:shadow-pink-900/20 hover:shadow-xl"
                    :class="viewMode === 'list' ? 'md:flex md:flex-row' : ''"
                    style="background-color: #111827;"
                >
                    <a href="{{ route('profile.show', array_merge(['slug' => $profile['slug']], request()->query())) }}" class="absolute inset-0 z-10" aria-label="View profile for {{ $profile['name'] }}"></a>

                    {{-- Image --}}
                    <div class="view-card-media relative overflow-hidden" :class="viewMode === 'list' ? 'md:w-64 md:shrink-0' : ''">
                        @if($profile['image'])
                            <img
                                src="{{ $profile['image'] }}"
                                alt="{{ $profile['name'] }}"
                                class="view-card-image w-full object-cover transition-transform duration-500 group-hover:scale-110"
                                :class="viewMode === 'list' ? 'h-56 md:h-full' : 'h-52'"
                                loading="lazy"
                            >
                        @else
                            <div class="flex items-center justify-center bg-gray-800 text-gray-600" :class="viewMode === 'list' ? 'h-56 md:h-full' : 'h-52'">
                                <i class="fa-solid fa-image text-4xl"></i>
                            </div>
                        @endif

                        {{-- Gradient overlay --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-gray-900/80 via-transparent to-transparent pointer-events-none"></div>

                        {{-- Badges --}}
                        <div class="absolute left-2 top-2 z-10 flex flex-wrap gap-1">
                            @if($profile['verified'])
                                <span class="inline-flex items-center gap-1 rounded-md bg-cyan-600/90 backdrop-blur-sm px-2 py-0.5 text-[10px] font-semibold text-white shadow">
                                    <i class="fa-solid fa-shield-check text-[8px]"></i> Verified
                                </span>
                            @endif
                            @if($profile['active'])
                                <span class="inline-flex items-center gap-1 rounded-md bg-emerald-600/90 backdrop-blur-sm px-2 py-0.5 text-[10px] font-semibold text-white shadow">
                                    <span class="h-1.5 w-1.5 rounded-full bg-white animate-pulse"></span> Online
                                </span>
                            @endif
                        </div>

                        {{-- Rate badge bottom-left of image --}}
                        <div class="absolute bottom-2 left-2 z-10">
                            <span class="rounded-lg bg-gray-900/80 backdrop-blur-sm px-2.5 py-1 text-xs font-bold text-white border border-white/10">
                                {{ $profile['rate'] }}
                            </span>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="p-3.5" :class="viewMode === 'list' ? 'flex flex-col justify-between flex-1 p-4' : ''">
                        <div>
                            <div class="mb-1 flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-white truncate" :class="viewMode === 'list' ? 'md:text-xl' : ''">
                                    {{ $profile['name'] }}
                                    <span class="font-normal text-gray-400">({{ $profile['age'] }})</span>
                                </h3>
                            </div>

                            <p class="mb-2.5 line-clamp-2 text-xs leading-relaxed text-gray-400" :class="viewMode === 'list' ? 'md:line-clamp-3 md:text-sm' : ''">
                                {{ $profile['description'] ?? '' }}
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 border-t border-gray-800 pt-2.5 text-[11px] text-gray-500" :class="viewMode === 'list' ? 'md:gap-x-4 md:text-xs' : ''">
                            @if($profile['city'])
                                <span class="inline-flex items-center gap-1"><i class="fa-solid fa-location-dot text-pink-500 text-[9px]"></i> {{ $profile['city'] }}</span>
                            @endif
                            @if(!empty($profile['service_1']))
                                <span class="inline-flex items-center gap-1"><i class="fa-solid fa-star text-yellow-500 text-[9px]"></i> {{ $profile['service_1'] }}</span>
                            @endif
                            @if(!empty($profile['service_2']))
                                <span class="inline-flex items-center gap-1"><i class="fa-solid fa-gem text-purple-400 text-[9px]"></i> {{ $profile['service_2'] }}</span>
                            @endif
                            <span class="ml-auto text-gray-600 text-[10px]">{{ $profile['date'] }}</span>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-gray-100 p-12 text-center">
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
            {{ $profiles->links() }}
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

    /* Pagination light theme override */
    nav[aria-label="Pagination"] span,
    nav[aria-label="Pagination"] a {
        background-color: #ffffff !important;
        border-color: #d1d5db !important;
        color: #374151 !important;
    }

    nav[aria-label="Pagination"] a:hover {
        background-color: #f3f4f6 !important;
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

