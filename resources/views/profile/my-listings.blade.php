@extends('layouts.frontend')

@section('title', 'My Listings')

@section('content')
@php
    $listings = $listings ?? collect();
    $profiles = $profiles ?? collect();
    $status = $status ?? request('status', 'all');
    $sort = $sort ?? request('sort', 'oldest');
    $searchQuery = $searchQuery ?? request('q');
    $totalListings = $listings->count() ?: $profiles->count();

    $tabs = [
        'all' => 'All Listings',
        'online' => 'Online',
        'expiring' => 'Expiring',
        'expired' => 'Expired',
        'offline' => 'Offline',
    ];
@endphp

<div class="min-h-screen bg-gray-50">
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="min-h-[600px] bg-white p-4 sm:p-6">

            <div class="mb-2 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50">
                    <i class="fa-solid fa-rectangle-list text-xl text-blue-700"></i>
                </div>

                <h1 class="text-xl font-bold text-blue-800">My Listings</h1>
            </div>

            <p class="mb-6 text-sm text-gray-600">
                Manage all of your Listings in one place, renew expiring or expired Listings or add Premium Features.
            </p>

            <div class="mb-6 flex flex-wrap gap-6 border-b border-gray-300">
                @foreach($tabs as $key => $label)
                    <a href="{{ route('my-listings', ['status' => $key, 'sort' => $sort, 'q' => $searchQuery]) }}"
                       class="pb-2 text-sm transition
                            {{ $status === $key
                                ? 'border-b-2 border-blue-600 font-medium text-blue-600'
                                : 'text-gray-600 hover:text-blue-600' }}">
                        {{ $label }}
                        @if(isset($statusCounts[$key]))
                            ({{ $statusCounts[$key] }})
                        @endif
                    </a>
                @endforeach
            </div>

            @if(session('success'))
                <div class="mb-5 rounded border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <span class="text-sm font-medium text-black">
                    {{ $totalListings }} Total Listings
                </span>

                <form method="GET" action="{{ route('my-listings') }}" class="flex flex-wrap items-center gap-4">
                    <input type="hidden" name="status" value="{{ $status }}">

                    <div class="relative">
                            <i class="fa-solid fa-search pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-base text-gray-400"></i>

                            <input
                                name="q"
                                value="{{ $searchQuery }}"
                                type="search"
                                placeholder="Search"
                                class="h-12 w-64 rounded-md border border-gray-300 bg-white pl-12 pr-4 text-base text-gray-700 placeholder:text-gray-400 focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600"
                            >
                        </div>

                    @if(filled($searchQuery))
                        <a href="{{ route('my-listings', ['status' => $status, 'sort' => $sort]) }}"
                           class="flex h-12 items-center rounded-md border border-gray-300 px-5 text-base font-semibold text-gray-700 hover:bg-gray-50">
                            Clear
                        </a>
                    @endif

                    <select
                        id="sort"
                        name="sort"
                        onchange="this.form.submit()"
                        class="h-12 rounded-md border border-blue-600 bg-blue-600 px-6 text-base font-semibold text-white focus:outline-none focus:ring-2 focus:ring-blue-200"
                    >
                        <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>
                            Sort by: Upload
                        </option>
                        <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>
                            Sort by: Newest
                        </option>
                    </select>
                </form>
            </div>

            @if($listings->isNotEmpty())
                <div class="space-y-4">
                    @foreach($listings as $listing)
                        @php
                            $isOnline = (bool) ($listing->is_live && $listing->is_active);
                            $badgeClass = $isOnline ? 'bg-green-600 text-white' : 'bg-gray-600 text-white';
                            $badgeLabel = $isOnline ? 'Online' : 'Offline';

                            $location = $listing->providerProfile?->suburb;
                            $category = $listing->category?->name ?? $listing->category_name ?? null;
                            $description = $listing->description ?? $listing->bio ?? null;

                            $likes = $listing->likes_count ?? 0;
                            $comments = $listing->comments_count ?? 0;
                            $views = $listing->views_count ?? 0;
                        @endphp

                        <article class="overflow-hidden rounded border border-gray-200 bg-white shadow-sm">
                            <div class="p-4">
                                <div class="flex gap-4">
                                    <div class="relative shrink-0">
                                        <div class="h-32 w-32 overflow-hidden rounded bg-gray-200">
                                            @if($listing->thumbnail)
                                                <img src="{{ url($listing->thumbnail) }}"
                                                     alt="{{ $listing->title }}"
                                                     class="h-full w-full object-cover">
                                            @else
                                                <div class="flex h-full w-full items-center justify-center">
                                                    <i class="fa-regular fa-image text-3xl text-gray-400"></i>
                                                </div>
                                            @endif
                                        </div>

                                        <span class="absolute left-2 top-2 rounded px-2 py-1 text-xs font-semibold {{ $badgeClass }}">
                                            {{ $badgeLabel }}
                                        </span>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="mb-2 flex items-start justify-between gap-3">
                                            <div>
                                                <h2 class="mb-1 font-bold text-blue-700">
                                                    {{ $listing->title }}
                                                </h2>

                                                @if($description)
                                                    <p class="mb-2 line-clamp-2 text-sm text-gray-700">
                                                        {{ $description }}
                                                    </p>
                                                @endif

                                                <div class="flex flex-wrap items-center gap-3 text-sm text-gray-600">
                                                    @if($category)
                                                        <span>Category: {{ $category }}</span>
                                                    @endif

                                                    @if($location)
                                                        <span class="flex items-center gap-1">
                                                            <i class="fa-solid fa-location-dot text-gray-500"></i>
                                                            {{ $location }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div x-data="{ open: false }" class="relative shrink-0">
                                                <button
                                                    type="button"
                                                    @click="open = !open"
                                                    @click.outside="open = false"
                                                    class="rounded p-1 text-gray-600 hover:bg-gray-100"
                                                >
                                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                                </button>

                                                <div
                                                    x-cloak
                                                    x-show="open"
                                                    x-transition
                                                    class="absolute right-0 top-8 z-20 w-44 overflow-hidden rounded border border-gray-200 bg-white shadow-lg"
                                                    style="display:none;"
                                                >
                                                    <a href="{{ route('my-listings.show', $listing) }}"
                                                       class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
                                                        View Details
                                                    </a>

                                                    <form action="{{ route('my-listings.feature', $listing) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="feature" value="top">
                                                        <button type="submit" class="block w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-50">
                                                            Top Feature
                                                        </button>
                                                    </form>

                                                    <form action="{{ route('my-listings.feature', $listing) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="feature" value="premium">
                                                        <button type="submit" class="block w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-50">
                                                            Upgrade Premium
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3 flex items-center gap-4 text-sm text-gray-600">
                                            <span class="flex items-center gap-1">
                                                <i class="fa-regular fa-heart"></i>
                                                {{ $likes }}
                                            </span>

                                            <span class="flex items-center gap-1">
                                                <i class="fa-regular fa-comment"></i>
                                                {{ $comments }}
                                            </span>

                                            <span class="flex items-center gap-1">
                                                <i class="fa-regular fa-eye"></i>
                                                {{ $views }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-blue-200 bg-blue-50 p-4">
                                <div class="mb-3 flex items-center gap-2">
                                    <i class="fa-solid fa-circle-info text-sm" style="color:#1d4ed8;"></i>

                                    <span class="text-sm font-semibold" style="color:#1e3a8a;">
                                        Get more views
                                    </span>
                                </div>

                                <p class="mb-3 text-sm text-gray-700">
                                    Add a Premium Feature to stand out in the search results!
                                </p>

                                <div class="flex flex-col gap-4 md:flex-row">

    <a href="{{ route('profiles.switch', $profile) }}"
       class="flex flex-1 items-center justify-center gap-2 rounded-md border-2 border-yellow-500 bg-white px-4 py-3 text-[15px] font-semibold text-yellow-600 transition">
        <i class="fa-solid fa-star text-yellow-500"></i>
        <span>Top</span>
    </a>

    <a href="{{ route('photos') }}"
       class="flex flex-1 items-center justify-center gap-2 rounded-md border-2 border-teal-700 bg-teal-600 px-4 py-3 text-[15px] font-semibold text-white transition hover:bg-teal-700">
        <i class="fa-regular fa-image text-white"></i>
        <span>Gallery</span>
    </a>

    <a href="{{ route('featured') }}"
       class="flex flex-1 items-center justify-center gap-2 rounded-md border-2 border-purple-700 bg-purple-600 px-4 py-3 text-[15px] font-semibold text-white transition hover:bg-purple-700">
        <i class="fa-solid fa-award text-white"></i>
        <span>Premium</span>
    </a>

</div>
                            </div>

                            <div class="flex justify-end border-t border-gray-200 bg-white px-4 py-2">
                                <a href="{{ route('my-listings.show', $listing) }}"
                                   class="rounded bg-blue-600 px-6 py-2 text-sm font-medium text-white hover:bg-blue-700">
                                    Details &#9654;
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

            @elseif($profiles->isNotEmpty())
                <div class="space-y-4">
                    @foreach($profiles as $profile)
                        @php
                            $isOnline = method_exists($profile, 'isCurrentlyOnline') ? $profile->isCurrentlyOnline() : false;
                            $badgeClass = $isOnline ? 'bg-green-600 text-white' : 'bg-gray-600 text-white';
                            $badgeLabel = $isOnline ? 'Online' : 'Offline';

                            $likes = $profile->likes_count ?? 0;
                            $comments = $profile->comments_count ?? 0;
                            $views = $profile->views_count ?? 0;
                        @endphp

                        <article class="overflow-hidden rounded border border-gray-200 bg-white shadow-sm">
                            <div class="p-4">
                                <div class="flex gap-4">
                                    <div class="relative shrink-0">
                                        <div class="h-32 w-32 overflow-hidden rounded bg-gray-200">
                                            @if($profile->primaryProfileImage)
                                                <img src="{{ $profile->primaryProfileImage->thumbnail_url }}"
                                                     alt="{{ $profile->name }}"
                                                     class="h-full w-full object-cover">
                                            @else
                                                <div class="flex h-full w-full items-center justify-center">
                                                    <i class="fa-regular fa-image text-3xl text-gray-400"></i>
                                                </div>
                                            @endif
                                        </div>

                                        <span class="absolute left-2 top-2 rounded px-2 py-1 text-xs font-semibold {{ $badgeClass }}">
                                            {{ $badgeLabel }}
                                        </span>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="mb-2 flex items-start justify-between gap-3">
                                            <div>
                                                <h2 class="mb-1 font-bold text-blue-700">
                                                    {{ $profile->name }}
                                                </h2>

                                                @if($profile->bio ?? false)
                                                    <p class="mb-2 line-clamp-2 text-sm text-gray-700">
                                                        {{ $profile->bio }}
                                                    </p>
                                                @endif

                                                <div class="flex flex-wrap items-center gap-3 text-sm text-gray-600">
                                                    @if($profile->suburb)
                                                        <span class="flex items-center gap-1">
                                                            <i class="fa-solid fa-location-dot text-gray-500"></i>
                                                            {{ $profile->suburb }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <button type="button" class="rounded p-1 text-gray-600 hover:bg-gray-100">
                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                            </button>
                                        </div>

                                        <div class="mb-3 flex items-center gap-4 text-sm text-gray-600">
                                            <span class="flex items-center gap-1">
                                                <i class="fa-regular fa-heart"></i>
                                                {{ $likes }}
                                            </span>

                                            <span class="flex items-center gap-1">
                                                <i class="fa-regular fa-comment"></i>
                                                {{ $comments }}
                                            </span>

                                            <span class="flex items-center gap-1">
                                                <i class="fa-regular fa-eye"></i>
                                                {{ $views }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-blue-200 bg-blue-50 p-4">
                                <div class="mb-3 flex items-center gap-2">
                                        <i class="fa-solid fa-circle-info text-sm" style="color:#1d4ed8;"></i>

                                        <span class="text-sm font-semibold" style="color:#1e3a8a;">
                                            Get more views
                                        </span>
                                    </div>

                                <p class="mb-3 text-sm text-gray-700">
                                    Add a Premium Feature to stand out in the search results!
                                </p>

                                <div class="flex flex-col gap-4 md:flex-row">
                                    <a href="{{ route('profiles.switch', $profile) }}"
                                       class="flex h-12 flex-1 items-center justify-center gap-3 rounded-md border-2 border-yellow-500 bg-white px-4 text-base font-semibold text-yellow-700 hover:bg-yellow-50">
                                        <i class="fa-solid fa-star text-lg text-yellow-500"></i>
                                        Top
                                    </a>

                                    <a href="{{ route('photos') }}"
                                       class="flex h-12 flex-1 items-center justify-center gap-3 rounded-md border-2 border-teal-700 bg-teal-600 px-4 text-base font-semibold text-white hover:bg-teal-700">
                                        <i class="fa-regular fa-image text-lg text-white"></i>
                                        Gallery
                                    </a>

                                    <a href="{{ route('featured') }}"
                                       class="flex h-12 flex-1 items-center justify-center gap-3 rounded-md border-2 border-purple-700 bg-purple-600 px-4 text-base font-semibold text-white hover:bg-purple-700">
                                        <i class="fa-solid fa-award text-lg text-white"></i>
                                        Premium
                                    </a>
                                </div>
                            </div>

                            <div class="flex justify-end border-t border-gray-200 bg-white px-4 py-2">
                                <a href="{{ route('my-listings.profile.show', $profile) }}"
                                   class="rounded bg-blue-600 px-6 py-2 text-sm font-medium text-white hover:bg-blue-700">
                                    Details &#9654;
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

            @else
                <div class="rounded border border-dashed border-gray-300 bg-white p-12 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded bg-gray-100">
                        <i class="fa-regular fa-rectangle-list text-2xl text-gray-400"></i>
                    </div>

                    <h2 class="text-lg font-semibold text-gray-900">
                        No listings yet
                    </h2>

                    <p class="mt-2 text-sm text-gray-500">
                        You don't have any listings yet. Head to the dashboard to create your first one.
                    </p>

                    <a href="{{ route('my-profile') }}"
                       class="mt-6 inline-flex items-center gap-2 rounded bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
                        <i class="fa-solid fa-plus"></i>
                        Go to Dashboard
                    </a>
                </div>
            @endif
        </div>
    </main>
</div>
@endsection
