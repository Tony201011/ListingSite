@extends('layouts.frontend')

@section('title', 'My Listings')

@section('content')
<div class="min-h-screen bg-gray-50 px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">

        {{-- Header --}}
        <div class="mb-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-pink-50">
                    <i class="fa-solid fa-rectangle-list text-xl text-[#e04ecb]"></i>
                </div>

                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">My Listings</h1>
                    <p class="mt-2 text-sm text-gray-500">
                        Manage all of your Listings in one place or add Premium Features.
                    </p>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="mt-5 flex flex-wrap items-center gap-6 border-b border-gray-200 text-sm font-medium">
                @foreach(['all' => 'All Listings', 'online' => 'Online', 'offline' => 'Offline'] as $key => $label)
                    <a href="{{ request()->fullUrlWithQuery(['status' => $key]) }}"
                       class="-mb-px pb-3 transition
                           {{ $status === $key
                               ? 'border-b-2 border-[#e04ecb] text-[#e04ecb]'
                               : 'border-b-2 border-transparent text-gray-600 hover:text-[#e04ecb]' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        @if(session('success'))
            <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-5 py-3 text-sm font-medium text-green-800">
                {{ session('success') }}
            </div>
        @endif

        {{-- Count/Search/Sort --}}
        <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm font-bold text-gray-900">
                {{ $listings->count() ?: ($profiles->count() ?? 0) }} Total Listings
            </p>

            <form method="GET" action="{{ route('my-listings') }}" class="flex flex-wrap items-center gap-3">
                <input type="hidden" name="status" value="{{ $status }}">

                <div class="relative">
                    <i class="fa-solid fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                    <input
                        name="q"
                        value="{{ $searchQuery ?? '' }}"
                        type="search"
                        placeholder="Search"
                        class="h-10 w-56 rounded-full border border-gray-300 bg-white pl-9 pr-3 text-sm text-gray-700 focus:border-[#e04ecb] focus:outline-none focus:ring-1 focus:ring-[#e04ecb]"
                    >
                </div>

                <select
                    id="sort"
                    name="sort"
                    onchange="this.form.submit()"
                    class="h-10 rounded-full border border-[#e04ecb] bg-[#e04ecb] px-4 text-sm font-semibold text-white focus:outline-none focus:ring-2 focus:ring-pink-200"
                >
                    <option value="oldest" {{ ($sort ?? 'oldest') === 'oldest' ? 'selected' : '' }}>
                        Sort by: Upload (oldest first)
                    </option>
                    <option value="newest" {{ ($sort ?? '') === 'newest' ? 'selected' : '' }}>
                        Sort by: Upload (newest first)
                    </option>
                </select>
            </form>
        </div>

        @if($listings->isNotEmpty())
            <div class="space-y-4">
                @foreach($listings as $listing)
                    @php
                        $isOnline = $listing->is_live && $listing->is_active;

                        if ($isOnline) {
                            $badgeClass = 'bg-green-500 text-white';
                            $badgeLabel = 'Online';
                        } else {
                            $badgeClass = 'bg-gray-600 text-white';
                            $badgeLabel = 'Offline';
                        }

                        $location = $listing->providerProfile?->suburb ?? null;
                    @endphp

                    <article class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                        <div class="relative p-4">
                            <div class="flex gap-4">
                                {{-- Image --}}
                                <div class="relative h-28 w-28 shrink-0 overflow-hidden rounded-md bg-gray-200">
                                    @if($listing->thumbnail)
                                        <img src="{{ url($listing->thumbnail) }}"
                                             alt="{{ $listing->title }}"
                                             class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center">
                                            <i class="fa-regular fa-image text-3xl text-gray-400"></i>
                                        </div>
                                    @endif

                                    <span class="absolute left-2 top-2 rounded px-2 py-1 text-xs font-semibold {{ $badgeClass }}">
                                        {{ $badgeLabel }}
                                    </span>
                                </div>

                                {{-- Content --}}
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <h2 class="text-xl font-semibold text-gray-900">
                                                {{ $listing->title }}
                                            </h2>

                                            <p class="mt-1 line-clamp-2 text-sm leading-6 text-gray-700">
                                                {{ $listing->description ?? 'No description available.' }}
                                            </p>
                                        </div>

                                        <div x-data="{ open: false }" class="relative shrink-0">
                                            <button
                                                type="button"
                                                @click="open = !open"
                                                @click.outside="open = false"
                                                class="flex h-8 w-8 items-center justify-center rounded-full text-gray-500 hover:bg-gray-100"
                                            >
                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                            </button>

                                            <div
                                                x-cloak
                                                x-show="open"
                                                x-transition
                                                class="absolute right-0 top-9 z-20 w-44 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg"
                                                style="display:none;"
                                            >
                                                <a href="{{ route('my-listings.show', $listing) }}"
                                                   class="block px-4 py-3 text-sm text-black hover:bg-gray-50">
                                                    View Details
                                                </a>

                                                <form action="{{ route('my-listings.feature', $listing) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="feature" value="top">
                                                    <button type="submit" class="block w-full px-4 py-3 text-left text-sm text-black hover:bg-gray-50">
                                                        Mark Online
                                                    </button>
                                                </form>

                                                <form action="{{ route('my-listings.feature', $listing) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="feature" value="premium">
                                                    <button type="submit" class="block w-full px-4 py-3 text-left text-sm text-black hover:bg-gray-50">
                                                        Upgrade Premium
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-gray-600">
                                        <span>Category: {{ $listing->category ?: 'Uncategorized' }}</span>

                                        @if($location)
                                            <span class="inline-flex items-center gap-1">
                                                <i class="fa-solid fa-location-dot text-gray-500"></i>
                                                {{ $location }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="mt-2 flex flex-wrap items-center gap-4 text-sm text-gray-600">
                                        <span class="inline-flex items-center gap-1">
                                            <i class="fa-regular fa-heart"></i> 0
                                        </span>
                                        <span class="inline-flex items-center gap-1">
                                            <i class="fa-regular fa-comment"></i> 0
                                        </span>
                                        <span class="inline-flex items-center gap-1">
                                            <i class="fa-regular fa-eye"></i> 0
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Promotion Bar --}}
                        <div class="border-t border-pink-100 bg-pink-50 px-4 py-3">
                            <p class="mb-2 flex items-center gap-2 text-sm font-semibold text-pink-700">
                                <i class="fa-regular fa-circle-question"></i>
                                Get more views
                            </p>

                            <p class="mb-3 text-sm text-gray-700">
                                Add a Premium Feature to stand out in the search results!
                            </p>

                            <div class="grid gap-3 md:grid-cols-3">
                                <form action="{{ route('my-listings.feature', $listing) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="feature" value="top">
                                    <button type="submit"
                                            class="h-10 w-full rounded-full border border-yellow-400 bg-white text-sm font-semibold text-yellow-700 hover:bg-yellow-50">
                                        <i class="fa-solid fa-star mr-1"></i>
                                        Top
                                    </button>
                                </form>

                                <a href="{{ route('photos') }}"
                                   class="flex h-10 items-center justify-center rounded-full border border-teal-700 bg-teal-600 text-sm font-semibold text-white hover:bg-teal-700">
                                    <i class="fa-regular fa-image mr-1"></i>
                                    Gallery
                                </a>

                                <form action="{{ route('my-listings.feature', $listing) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="feature" value="premium">
                                    <button type="submit"
                                            class="h-10 w-full rounded-full border border-purple-700 bg-purple-600 text-sm font-semibold text-white hover:bg-purple-700">
                                        <i class="fa-solid fa-award mr-1"></i>
                                        Premium
                                    </button>
                                </form>
                            </div>

                            <div class="mt-4 flex justify-end">
                                <a href="{{ route('my-listings.show', $listing) }}"
                                   class="inline-flex h-9 items-center gap-2 rounded-full bg-[#e04ecb] px-5 text-sm font-semibold text-white hover:bg-[#c13ab0]">
                                    Details
                                    <i class="fa-solid fa-caret-right"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

        @elseif(isset($profiles) && $profiles->isNotEmpty())
            <div class="space-y-4">
                @foreach($profiles as $profile)
                    <article class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                        <div class="relative p-4">
                            <div class="flex gap-4">
                                <div class="relative h-28 w-28 shrink-0 overflow-hidden rounded-md bg-gray-200">
                                    @if($profile->primaryProfileImage)
                                        <img src="{{ $profile->primaryProfileImage->thumbnail_url }}"
                                             alt="{{ $profile->name }}"
                                             class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center">
                                            <i class="fa-regular fa-image text-3xl text-gray-400"></i>
                                        </div>
                                    @endif

                                    <span class="absolute left-2 top-2 rounded bg-green-500 px-2 py-1 text-xs font-semibold text-white">
                                        {{ $profile->isCurrentlyOnline() ? 'Online' : 'Offline' }}
                                    </span>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <h2 class="text-xl font-semibold text-gray-900">
                                                {{ $profile->name }}
                                            </h2>

                                            <p class="mt-1 line-clamp-2 text-sm leading-6 text-gray-700">
                                                {{ $profile->description ?? 'No description available.' }}
                                            </p>
                                        </div>

                                        <button type="button" class="flex h-8 w-8 items-center justify-center rounded-full text-gray-500 hover:bg-gray-100">
                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                        </button>
                                    </div>

                                    <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-gray-600">
                                        <span>Category: {{ $profile->category ?? 'Uncategorized' }}</span>

                                        @if($profile->suburb)
                                            <span class="inline-flex items-center gap-1">
                                                <i class="fa-solid fa-location-dot text-gray-500"></i>
                                                {{ $profile->suburb }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="mt-2 flex flex-wrap items-center gap-4 text-sm text-gray-600">
                                        <span class="inline-flex items-center gap-1">
                                            <i class="fa-regular fa-heart"></i> 0
                                        </span>
                                        <span class="inline-flex items-center gap-1">
                                            <i class="fa-regular fa-comment"></i> 0
                                        </span>
                                        <span class="inline-flex items-center gap-1">
                                            <i class="fa-regular fa-eye"></i> 0
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-pink-100 bg-pink-50 px-4 py-3">
                            <p class="mb-2 flex items-center gap-2 text-sm font-semibold text-pink-700">
                                <i class="fa-regular fa-circle-question"></i>
                                Get more views
                            </p>

                            <p class="mb-3 text-sm text-gray-700">
                                Add a Premium Feature to stand out in the search results!
                            </p>

                            <div class="grid gap-3 md:grid-cols-3">
                                <a href="{{ route('profiles.switch', $profile) }}"
                                   class="flex h-10 items-center justify-center rounded-full border border-yellow-400 bg-white text-sm font-semibold text-yellow-700 hover:bg-yellow-50">
                                    <i class="fa-solid fa-star mr-1"></i>
                                    Top
                                </a>

                                <a href="{{ route('photos') }}"
                                   class="flex h-10 items-center justify-center rounded-full border border-teal-700 bg-teal-600 text-sm font-semibold text-white hover:bg-teal-700">
                                    <i class="fa-regular fa-image mr-1"></i>
                                    Gallery
                                </a>

                                <a href="{{ route('featured') }}"
                                   class="flex h-10 items-center justify-center rounded-full border border-purple-700 bg-purple-600 text-sm font-semibold text-white hover:bg-purple-700">
                                    <i class="fa-solid fa-award mr-1"></i>
                                    Premium
                                </a>
                            </div>

                            <div class="mt-4 flex justify-end">
                                <a href="{{ route('profiles.switch', $profile) }}"
                                   class="inline-flex h-9 items-center gap-2 rounded-full bg-[#e04ecb] px-5 text-sm font-semibold text-white hover:bg-[#c13ab0]">
                                    Details
                                    <i class="fa-solid fa-caret-right"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

        @else
            <div class="rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-gray-100">
                    <i class="fa-regular fa-rectangle-list text-2xl text-gray-400"></i>
                </div>

                <h2 class="text-lg font-semibold text-gray-900">No listings yet</h2>
                <p class="mt-2 text-sm text-gray-500">
                    You don't have any listings yet. Head to the dashboard to create your first one.
                </p>

                <a href="{{ route('my-profile') }}"
                   class="mt-6 inline-flex items-center gap-2 rounded-full bg-[#e04ecb] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#c13ab0]">
                    <i class="fa-solid fa-plus"></i>
                    Go to Dashboard
                </a>
            </div>
        @endif

    </div>
</div>
@endsection