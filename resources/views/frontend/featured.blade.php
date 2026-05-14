@extends('layouts.frontend')

@section('title', 'Featured Escorts')

@section('content')
<div class="min-h-screen bg-gray-100 text-gray-800">

    {{-- Page Header --}}
    <div class="bg-gray-950 border-b border-gray-800">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-white sm:text-3xl">Featured Escorts</h1>
            <p class="mt-1 text-sm text-gray-400">Our highlighted and promoted profiles — handpicked for visibility.</p>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"
        x-data="favouriteBookmark({
            favourites: {{ Js::from($userFavourites ?? []) }},
            bookmarks: []
        })"
    >

        @php
            $hasAny = $homeBannerProfiles->isNotEmpty()
                || $homeFeaturedProfiles->isNotEmpty()
                || $localBannerProfiles->isNotEmpty()
                || $featuredProfiles->isNotEmpty();
        @endphp

        @if(!$hasAny)
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-12 text-center">
                <i class="fa-solid fa-star mb-4 text-3xl text-gray-400"></i>
                <p class="text-sm font-medium text-gray-600">No featured profiles at this time. Check back soon!</p>
                <a href="{{ url('/') }}" class="mt-4 inline-block rounded-lg bg-pink-600 px-5 py-2 text-sm font-semibold text-white hover:bg-pink-700 transition">Browse all escorts</a>
            </div>
        @else

            {{-- Home Page Spotlight (home banner) --}}
            @if($homeBannerProfiles->isNotEmpty())
                <div class="mb-8">
                    <div class="mb-3 flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-purple-600 to-indigo-600 px-3 py-1 text-xs font-bold uppercase tracking-wider text-white shadow">
                            <i class="fa-solid fa-crown text-[10px]"></i> Home Page Spotlight
                        </span>
                        <span class="text-xs text-gray-500">National — shown at the top of the home page</span>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                        @foreach($homeBannerProfiles as $profile)
                            @include('frontend.partials.profile-card', ['profile' => $profile, 'tierBadgeVariant' => 'home_banner'])
                        @endforeach
                    </div>
                </div>
                <hr class="mb-8 border-gray-200">
            @endif

            {{-- Home Page Featured --}}
            @if($homeFeaturedProfiles->isNotEmpty())
                <div class="mb-8">
                    <div class="mb-3 flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-pink-500 to-rose-500 px-3 py-1 text-xs font-bold uppercase tracking-wider text-white shadow">
                            <i class="fa-solid fa-star text-[10px]"></i> Home Page Featured
                        </span>
                        <span class="text-xs text-gray-500">Top of the home page listing grid</span>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                        @foreach($homeFeaturedProfiles as $profile)
                            @include('frontend.partials.profile-card', ['profile' => $profile])
                        @endforeach
                    </div>
                </div>
                <hr class="mb-8 border-gray-200">
            @endif

            {{-- Local Spotlight --}}
            @if($localBannerProfiles->isNotEmpty())
                <div class="mb-8">
                    <div class="mb-3 flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-amber-500 to-orange-500 px-3 py-1 text-xs font-bold uppercase tracking-wider text-white shadow">
                            <i class="fa-solid fa-location-dot text-[10px]"></i> Local Spotlight
                        </span>
                        <span class="text-xs text-gray-500">State-specific banner profiles</span>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                        @foreach($localBannerProfiles as $profile)
                            @include('frontend.partials.profile-card', ['profile' => $profile, 'tierBadgeVariant' => 'local_banner'])
                        @endforeach
                    </div>
                </div>
                <hr class="mb-8 border-gray-200">
            @endif

            {{-- Featured Badge --}}
            @if($featuredProfiles->isNotEmpty())
                <div class="mb-8">
                    <div class="mb-3 flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-yellow-400 to-amber-500 px-3 py-1 text-xs font-bold uppercase tracking-wider text-white shadow">
                            <i class="fa-solid fa-certificate text-[10px]"></i> Featured
                        </span>
                        <span class="text-xs text-gray-500">Featured badge profiles</span>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                        @foreach($featuredProfiles as $profile)
                            @include('frontend.partials.profile-card', ['profile' => $profile])
                        @endforeach
                    </div>
                </div>
            @endif

        @endif

        <div class="mt-6 text-center">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:border-pink-400 hover:text-pink-600">
                <i class="fa-solid fa-grid-2 text-xs"></i> Browse all escorts
            </a>
        </div>

    </div>
</div>
@endsection
