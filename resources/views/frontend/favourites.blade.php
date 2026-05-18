@extends('layouts.frontend')

@section('title', 'My Favourites')

@section('content')
<div class="min-h-screen bg-gray-100 text-gray-800">

    {{-- Page Header --}}
    <div class="bg-gray-950 border-b border-gray-800">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-heart text-pink-500 text-xl"></i>
                <div>
                    <h1 class="text-2xl font-bold text-white">My Favourites</h1>
                    <p class="text-sm text-gray-400 mt-0.5">Listings you've saved as favourites</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"
        x-data="favouriteBookmark({
            favourites: {{ Js::from($userFavourites ?? []) }},
            bookmarks: []
        })"
    >

        {{-- Profile Cards Grid --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5" x-show="favourites.length > 0 && {{ count($profiles) > 0 ? 'true' : 'false' }}">
            @forelse($profiles as $profile)
                <article
                    class="group relative overflow-hidden rounded-2xl bg-white shadow-sm border border-gray-200 transition-all duration-300 hover:shadow-md hover:border-gray-300 hover:-translate-y-0.5"
                    x-cloak
                    x-show="isFavourite('{{ $profile['slug'] }}')"
                >
                    <a href="{{ route('profile.show', ['slug' => $profile['slug']]) }}" class="absolute inset-0 z-10" aria-label="View profile for {{ $profile['name'] }}"></a>

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

                        @if(!empty($profile['home_banner']) || !empty($profile['home_featured']))
                            <x-featured-badge variant="glow" position="top-right" label="Featured" icon="crown" />
                        @elseif(!empty($profile['local_banner']))
                            <x-featured-badge variant="glow" position="top-right" label="Local" icon="star" />
                        @elseif(!empty($profile['featured']))
                            <x-featured-badge variant="minimal" position="top-right" />
                        @endif

                        {{-- Photo Verified / Available Now / Online badges --}}
                        <div class="absolute left-0 top-3 z-10 flex flex-col gap-1">
                            @if($profile['verified'])
                                <span class="inline-flex items-center gap-1 bg-cyan-500 px-2.5 py-1 text-[11px] font-semibold text-white shadow-sm" style="border-radius: 0 4px 4px 0;">
                                    <i class="fa-solid fa-camera text-[9px]"></i> Photo Verified
                                </span>
                            @endif
                            @if(!empty($profile['available_now']))
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold text-white shadow-sm" style="border-radius: 0 4px 4px 0; background-color: #e13a8b;">
                                    <span class="h-1.5 w-1.5 rounded-full bg-white animate-pulse"></span> Available Now
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
                                    title="Remove from favourites"
                                >
                                    <i :class="isFavourite('{{ $profile['slug'] }}') ? 'fa-solid fa-heart' : 'fa-regular fa-heart'" class="text-xs"></i>
                                </button>
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
                                    {{ $profile['suburb'] ?: $profile['city'] }}
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
                {{-- empty state shown by default --}}
            @endforelse
        </div>

        {{-- Empty state (shown when no favourites, or all were removed) --}}
        <div x-show="{{ empty($profiles) ? 'true' : 'favourites.length === 0' }}" class="rounded-2xl border border-dashed border-gray-300 bg-white p-16 text-center">
            <i class="fa-regular fa-heart mb-4 text-4xl text-gray-300"></i>
            <p class="text-base font-medium text-gray-600">You haven't saved any favourites yet.</p>
            <p class="mt-1 text-sm text-gray-500">Browse listings and click the <i class="fa-regular fa-heart text-pink-400"></i> icon to save them here.</p>
            <a href="{{ url('/') }}" class="mt-5 inline-block rounded-lg bg-pink-600 px-5 py-2 text-sm font-semibold text-white hover:bg-pink-700 transition">Browse Listings</a>
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
