@extends('layouts.frontend')

@section('title', 'Home')

@php
    $selectedCategoryIds = collect($selectedCategoryIds ?? []);
    $locationQuery = (string) ($locationQuery ?? '');
    $escortNameQuery = (string) ($escortNameQuery ?? '');
    $hasAgeFilter = $hasAgeFilter ?? false;
    $hasPriceFilter = $hasPriceFilter ?? false;
    $selectedCategoryItems = $selectedCategoryItems ?? collect();
@endphp

@section('content')
<div class="min-h-screen bg-gray-50 text-gray-800">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-4 flex items-center gap-2 text-xs text-gray-500">
            <span>Home</span>
            <span>›</span>
            <span class="text-gray-700">Girls</span>
        </div>

        <div class="mb-4 flex flex-wrap items-center gap-2 text-xs">
            <span class="text-gray-500">Showing {{ $profiles->total() }} profiles</span>
            @if($locationQuery !== '' || $escortNameQuery !== '' || collect($selectedCategoryItems)->isNotEmpty() || $hasAgeFilter || $hasPriceFilter)
                <a href="{{ url('/') }}" class="ml-auto text-gray-500 hover:text-gray-700">Clear all</a>
            @endif
        </div>

        <div x-data="{ viewMode: 'grid' }">
            <section>
                <div class="mb-4 flex flex-wrap items-center gap-3 border-b border-gray-200 pb-3">
                    <div class="flex items-center gap-2">
                        <button type="button" class="rounded-full border border-gray-300 bg-white px-4 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-100">New girls</button>
                        <button type="button" class="rounded-full border border-gray-900 bg-gray-100 px-4 py-1.5 text-xs font-semibold text-gray-900">All girls</button>
                    </div>

                    <div class="ml-2 flex items-center gap-1 text-xs text-gray-500">
                        <span>Popular</span>
                        <span>▾</span>
                    </div>

                    <div class="ml-auto flex items-center gap-3 text-xs text-gray-500">
                        <span class="hidden sm:inline">Compare (1)</span>
                        <button type="button" class="rounded border px-2 py-1 hover:bg-gray-100" :class="viewMode === 'list' ? 'border-gray-900 bg-gray-100 text-gray-900' : 'border-gray-300 text-gray-500'" @click="viewMode = 'list'">☰</button>
                        <button type="button" class="rounded border px-2 py-1 hover:bg-gray-100" :class="viewMode === 'grid' ? 'border-gray-900 bg-gray-100 text-gray-900' : 'border-gray-300 text-gray-500'" @click="viewMode = 'grid'">▦</button>
                    </div>
                </div>

                <div class="mb-4 rounded-xl border border-gray-200 bg-white p-5" x-data="{ searchMode: '{{ $escortNameQuery !== '' ? 'username' : 'suburb' }}', term: '{{ e($escortNameQuery !== '' ? $escortNameQuery : $locationQuery) }}' }">
                    <h3 class="mb-3 text-2xl font-bold text-gray-900" x-text="searchMode === 'username' ? 'Enter username to find escort' : 'Enter suburb to search local escorts'"></h3>
                    <form method="GET" action="{{ url('/') }}" class="space-y-3">
                        <input type="hidden" name="location" :value="searchMode === 'suburb' ? term : ''">
                        <input type="hidden" name="escort_name" :value="searchMode === 'username' ? term : ''">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <input
                                type="text"
                                x-model="term"
                                :placeholder="searchMode === 'username' ? 'Enter username' : 'Enter suburb'"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 placeholder:text-gray-400 focus:border-pink-400 focus:outline-none sm:w-[340px]"
                            >
                            <button type="submit" class="rounded-md bg-[#b58aac] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#a6749b] sm:min-w-[200px]">
                                Find Escort
                            </button>
                        </div>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <button
                                type="button"
                                @click="searchMode = searchMode === 'suburb' ? 'username' : 'suburb'; term = ''"
                                class="rounded-md bg-[#b58aac] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#a6749b] sm:min-w-[200px]"
                                x-text="searchMode === 'suburb' ? 'Search by Name' : 'Search by Suburb'"
                            >
                            </button>
                            <a href="{{ route('advanced-search') }}" class="inline-flex items-center justify-center rounded-md bg-[#b58aac] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#a6749b] sm:min-w-[200px]">
                                Advanced Search / Filter
                            </a>
                        </div>
                    </form>
                </div>

                <div class="mb-4 text-center">
                    <h2 class="text-lg font-bold tracking-wide text-gray-900">
                        Popular
                        <span class="uppercase"><span class="text-pink-600">Hot</span><span class="text-gray-900">escorts</span></span>
                        100% real and genuine escorts
                    </h2>
                </div>

                <div x-cloak class="grid gap-4" :class="viewMode === 'grid' ? 'sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5' : 'grid-cols-1'">
                    @forelse($profiles as $profile)
                        <article class="view-card relative overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" :class="viewMode === 'list' ? 'md:grid md:grid-cols-[300px_1fr]' : ''">
                            <a href="{{ route('profile.show', array_merge(['slug' => $profile['slug']], request()->query())) }}" class="absolute inset-0 z-10" aria-label="View profile for {{ $profile['name'] }}"></a>
                            <div class="view-card-media relative" :class="viewMode === 'list' ? 'md:h-[220px]' : ''">
                                <img src="{{ $profile['image'] }}" alt="{{ $profile['name'] }}" class="view-card-image object-cover" :class="viewMode === 'list' ? 'h-56 w-full md:h-[220px] md:w-full' : 'h-44 w-full'">
                                <div class="absolute left-2 top-2 z-10 flex gap-1 text-[10px] font-semibold">
                                    @if($profile['verified'])
                                        <span class="rounded bg-cyan-500 px-2 py-0.5 text-white">Photo Verified</span>
                                    @endif
                                    @if($profile['active'])
                                        <span class="rounded bg-emerald-500 px-2 py-0.5 text-white">Online</span>
                                    @endif
                                </div>
                            </div>

                            <div class="view-card-content p-3" :class="viewMode === 'list' ? 'md:flex md:flex-col md:justify-between md:px-4 md:py-3' : ''">
                                <div class="mb-1.5 flex items-center justify-between text-[10px] text-gray-400">
                                    <span>{{ $profile['date'] }}</span>
                                    <span>♡  ⎘  ⤴</span>
                                </div>

                                <h3 class="text-sm font-semibold text-gray-900" :class="viewMode === 'list' ? 'md:text-[22px] md:leading-6' : ''">{{ $profile['name'] }} <span class="text-gray-400">({{ $profile['age'] }})</span></h3>
                                <p class="mb-2 text-xl font-bold text-gray-900" :class="viewMode === 'list' ? 'md:text-3xl md:leading-8' : ''">{{ $profile['rate'] }}</p>
                                <p class="mb-3 text-xs text-gray-500" :class="viewMode === 'list' ? 'list-desc md:text-[12px] md:leading-5' : ''">{{ $profile['description'] ?? '' }}</p>

                                <div class="grid grid-cols-2 gap-2 border-t border-gray-100 pt-3 text-xs text-gray-500" :class="viewMode === 'list' ? 'md:grid-cols-4 md:gap-2 md:text-[11px]' : ''">
                                    <span>📍 {{ $profile['city'] }}</span>
                                    @if(!empty($profile['height']))
                                        <span>📏 {{ $profile['height'] }}</span>
                                    @endif
                                    @if(!empty($profile['service_1']))
                                        <span>💼 {{ $profile['service_1'] }}</span>
                                    @endif
                                    @if(!empty($profile['service_2']))
                                        <span>💎 {{ $profile['service_2'] }}</span>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-full rounded-xl border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500">
                            No profiles found for selected categories.
                        </div>
                    @endforelse
                </div>

                <div class="mt-8">
                    {{ $profiles->links() }}
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .view-card {
        transition: box-shadow 0.25s ease, transform 0.25s ease;
    }

    .view-card:hover {
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.12);
    }

    .view-card-media {
        overflow: hidden;
    }

    .view-card-media::after {
        content: '';
        position: absolute;
        inset: 0;
        pointer-events: none;
        background: linear-gradient(to top, rgba(10, 15, 28, 0.45) 0%, rgba(10, 15, 28, 0.1) 45%, rgba(10, 15, 28, 0) 100%);
        opacity: 0.35;
        transition: opacity 0.35s ease;
    }

    .view-card-image {
        transition: transform 0.45s ease;
        transform-origin: center center;
    }

    .view-card:hover .view-card-image {
        transform: scale(1.08);
    }

    .view-card:hover .view-card-media::after {
        opacity: 0.65;
    }

    .list-desc {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endpush

