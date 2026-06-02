@extends('layouts.frontend')

@section('title', 'My Listings')

@section('content')
<div class="min-h-screen bg-gray-50 px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-6xl">
        <div class="mb-8 flex flex-col gap-4 rounded-3xl border border-gray-200 bg-white p-6 shadow-sm sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">My Listings</h1>
                <p class="mt-2 max-w-2xl text-sm text-gray-500">
                    Manage your current provider listings in one place and see which ones are live.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="rounded-full border border-gray-200 bg-gray-50 px-4 py-2 text-sm font-semibold text-gray-900">
                    {{ $listings->count() }} Total Listings
                </div>
                <a href="{{ route('my-profile') }}" class="inline-flex items-center justify-center rounded-full bg-pink-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-pink-700">
                    View Dashboard
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-3xl border border-green-200 bg-green-50 px-6 py-4 text-sm font-medium text-green-800 shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-6 overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-5 py-5 sm:px-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-pink-600">Listings manager</p>
                        <h2 class="mt-2 text-2xl font-semibold text-gray-900">All Listings</h2>
                        <p class="mt-1 text-sm text-gray-500">Manage all of your listings, activate or promote them, and track performance at a glance.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="rounded-full border border-gray-200 bg-gray-50 px-4 py-2 text-sm font-semibold text-gray-900">
                            {{ $statusCounts['all'] ?? $listings->count() }} Total Listings
                        </div>
                        <a href="{{ route('my-profile') }}" class="inline-flex items-center justify-center rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">View Dashboard</a>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap items-center gap-2 border-t border-gray-200 pt-4">
                    @foreach(['all' => 'All Listings', 'online' => 'Online', 'expiring' => 'Expiring', 'expired' => 'Expired', 'offline' => 'Offline'] as $key => $label)
                        <a href="{{ request()->fullUrlWithQuery(['status' => $key]) }}"
                           class="inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold transition {{ $status === $key ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            {{ $label }}
                            <span class="ml-2 rounded-full bg-white px-2 py-0.5 text-[11px] font-semibold text-gray-700">{{ $statusCounts[$key] ?? 0 }}</span>
                        </a>
                    @endforeach
                </div>

                <form method="GET" action="{{ route('my-listings') }}" class="mt-6 grid gap-3 lg:grid-cols-[1fr_auto] lg:items-center">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <div class="relative w-full">
                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input name="q" value="{{ $searchQuery ?? '' }}" type="search" placeholder="Search by title" class="w-full rounded-full border border-gray-200 bg-gray-50 py-3 pl-12 pr-4 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div class="flex flex-wrap items-center gap-3 justify-end">
                        <label class="sr-only" for="sort">Sort</label>
                        <select id="sort" name="sort" class="rounded-full border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                            <option value="oldest" {{ ($sort ?? 'oldest') === 'oldest' ? 'selected' : '' }}>Upload (oldest first)</option>
                            <option value="newest" {{ ($sort ?? '') === 'newest' ? 'selected' : '' }}>Upload (newest first)</option>
                        </select>
                        <button type="submit" class="inline-flex items-center rounded-full bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">Apply</button>
                    </div>
                </form>
            </div>

            <div class="border-b border-gray-200 bg-blue-50 px-5 py-4 sm:px-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-blue-800">Promotion & Advertisements</p>
                        <p class="mt-1 text-sm text-blue-700">Promote listings, feature them on the homepage, or add premium exposure for better visibility.</p>
                    </div>
                    <div class="grid w-full gap-3 sm:w-auto sm:grid-cols-3">
                        <a href="{{ route('featured') }}" class="inline-flex items-center justify-center rounded-full border border-blue-200 bg-white px-4 py-3 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">Homepage Feature</a>
                        <a href="{{ route('featured') }}" class="inline-flex items-center justify-center rounded-full border border-teal-200 bg-white px-4 py-3 text-sm font-semibold text-teal-700 transition hover:bg-teal-100">Premium Listing</a>
                        <a href="{{ route('featured') }}" class="inline-flex items-center justify-center rounded-full border border-violet-200 bg-white px-4 py-3 text-sm font-semibold text-violet-700 transition hover:bg-violet-100">Gallery Boost</a>
                    </div>
                </div>
            </div>

            <div class="space-y-5 p-5 sm:p-6">
                @if($listings->isNotEmpty())
                    @forelse($listings as $listing)
                    <article class="overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-sm">
                        <div class="grid gap-4 lg:grid-cols-[280px_minmax(0,1fr)]">
                            <div class="relative overflow-hidden bg-gray-100">
                                @if($listing->thumbnail)
                                    <img src="{{ url($listing->thumbnail) }}" alt="{{ $listing->title }}" class="h-full min-h-[240px] w-full object-cover">
                                @else
                                    <div class="flex h-full min-h-[240px] items-center justify-center bg-gray-200 text-sm text-gray-500">
                                        No photo available
                                    </div>
                                @endif

                                <span class="absolute left-4 top-4 inline-flex rounded-full {{ $listing->is_live ? 'bg-green-600 text-white' : 'bg-gray-700 text-white' }} px-3 py-1 text-xs font-semibold">
                                    {{ $listing->is_live ? 'Online' : 'Offline' }}
                                </span>
                            </div>

                            <div class="flex flex-col justify-between p-5">
                                <div class="space-y-4">
                                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                        <div class="min-w-0">
                                            <h2 class="truncate text-2xl font-semibold text-blue-700 hover:text-blue-800">{{ $listing->title }}</h2>
                                            <p class="mt-2 text-sm text-gray-500">
                                                {{ $listing->category ?: 'Uncategorized' }} · {{ $listing->age ? $listing->age.' years' : 'Age not set' }}
                                            </p>
                                        </div>
                                        <span class="inline-flex items-center rounded-full border border-pink-100 bg-pink-50 px-3 py-1 text-xs font-semibold text-pink-700">
                                            {{ $listing->is_vip ? 'Premium' : 'Standard' }}
                                        </span>
                                    </div>

                                    <div class="space-y-3 text-sm text-gray-600">
                                        <p class="leading-6 text-gray-500">Manage your listing details and promotions from this page. View listing activity, update visibility, and push premium placement with the buttons below.</p>
                                        <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500">
                                            <span class="inline-flex items-center gap-2 rounded-2xl border border-gray-200 bg-gray-50 px-3 py-2">
                                                <i class="fa-solid fa-tag text-gray-500"></i>
                                                {{ $listing->category ?: 'Uncategorized' }}
                                            </span>
                                            <span class="inline-flex items-center gap-2 rounded-2xl border border-gray-200 bg-gray-50 px-3 py-2">
                                                <i class="fa-solid fa-globe text-gray-500"></i>
                                                {{ ucfirst($listing->website_type) }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500">
                                        <span class="inline-flex items-center gap-2 rounded-2xl border border-gray-200 bg-gray-50 px-3 py-2">
                                            <i class="fa-solid fa-star text-amber-500"></i>
                                            {{ number_format($listing->audience_score, 2) }} score
                                        </span>
                                        <span class="inline-flex items-center gap-2 rounded-2xl border border-gray-200 bg-gray-50 px-3 py-2">
                                            <i class="fa-solid fa-circle {{ $listing->is_active ? 'text-green-500' : 'text-gray-400' }} text-[10px]"></i>
                                            {{ $listing->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div class="rounded-3xl border border-gray-100 bg-gray-50 px-4 py-3 text-sm text-gray-600">
                                        Get more views. Add a Premium Feature to stand out in the search results!
                                    </div>

                                    <div class="grid gap-3 lg:grid-cols-[1fr_140px]">
                                        <div class="grid gap-3 sm:grid-cols-3">
                                            <form action="{{ route('my-listings.feature', $listing) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="feature" value="top">
                                                <button type="submit" class="w-full rounded-2xl border border-yellow-200 bg-yellow-50 px-4 py-2 text-sm font-semibold text-yellow-800 transition hover:bg-yellow-100">Top</button>
                                            </form>
                                            <a href="{{ route('photos') }}" class="inline-flex h-full items-center justify-center rounded-2xl border border-teal-200 bg-teal-50 px-4 py-2 text-sm font-semibold text-teal-800 transition hover:bg-teal-100">Gallery</a>
                                            <form action="{{ route('my-listings.feature', $listing) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="feature" value="premium">
                                                <button type="submit" class="w-full rounded-2xl border border-violet-200 bg-violet-50 px-4 py-2 text-sm font-semibold text-violet-800 transition hover:bg-violet-100">Premium</button>
                                            </form>
                                        </div>
                                        <a href="{{ route('my-listings.show', $listing) }}" class="inline-flex h-full items-center justify-center rounded-2xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">Details</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                    @empty
                        <div class="rounded-[28px] border border-dashed border-gray-200 bg-white p-10 text-center">
                            <h2 class="text-xl font-semibold text-gray-900">No listings yet</h2>
                            <p class="mt-2 text-sm text-gray-500">You don’t have any provider listings yet. Create your first listing from the dashboard to get started.</p>
                        </div>
                    @endforelse
                @else
                    @if(isset($profiles) && $profiles->isNotEmpty())
                        @foreach($profiles as $profile)
                            <article class="overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-sm">
                                <div class="grid gap-4 lg:grid-cols-[280px_minmax(0,1fr)]">
                                    <div class="relative overflow-hidden bg-gray-100">
                                        @if($profile->primaryProfileImage)
                                            <img src="{{ $profile->primaryProfileImage->thumbnail_url }}" alt="{{ $profile->name }}" class="h-full min-h-[240px] w-full object-cover">
                                        @else
                                            <div class="flex h-full min-h-[240px] items-center justify-center bg-gray-200 text-sm text-gray-500">
                                                No photo available
                                            </div>
                                        @endif

                                        <span class="absolute left-4 top-4 inline-flex rounded-full {{ $profile->isCurrentlyOnline() ? 'bg-green-600 text-white' : 'bg-gray-700 text-white' }} px-3 py-1 text-xs font-semibold">
                                            {{ $profile->isCurrentlyOnline() ? 'Online' : 'Offline' }}
                                        </span>
                                    </div>

                                    <div class="flex flex-col justify-between p-5">
                                        <div class="space-y-4">
                                            <div class="flex flex-wrap items-center justify-between gap-3">
                                                <div class="min-w-0">
                                                    <h2 class="truncate text-2xl font-semibold text-gray-900">{{ $profile->name }}</h2>
                                                    <p class="mt-2 text-sm text-gray-500">
                                                        {{ $profile->age ? $profile->age.' years' : 'Age not set' }} · {{ $profile->suburb ?? 'Location not set' }}
                                                    </p>
                                                </div>
                                                <span class="inline-flex rounded-full border border-pink-100 bg-pink-50 px-3 py-1 text-xs font-semibold text-pink-700">
                                                    {{ $profile->is_featured ? 'VIP' : 'Standard' }}
                                                </span>
                                            </div>

                                            <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500">
                                                <span class="inline-flex items-center gap-2 rounded-2xl border border-gray-200 bg-gray-50 px-3 py-2">
                                                    <i class="fa-regular fa-heart text-pink-500"></i>
                                                    {{ number_format($profile->user->providerListings()->count(), 0) }} listings
                                                </span>
                                                <span class="inline-flex items-center gap-2 rounded-2xl border border-gray-200 bg-gray-50 px-3 py-2">
                                                    {{ $profile->isCurrentlyAvailableNow() ? 'Available Now' : 'Not available' }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="space-y-4">
                                            <div class="rounded-3xl border border-gray-100 bg-gray-50 px-4 py-3 text-sm text-gray-600">
                                                Manage your profile and listings from the dashboard.
                                            </div>

                                            <div class="grid gap-3 lg:grid-cols-[1fr_140px]">
                                                <div class="grid gap-3 sm:grid-cols-3">
                                                    <a href="{{ route('profiles.switch', $profile) }}" class="w-full rounded-2xl border border-yellow-200 bg-yellow-50 px-4 py-2 text-sm font-semibold text-yellow-800 transition hover:bg-yellow-100">Top</a>
                                                    <a href="{{ route('photos') }}" class="inline-flex h-full items-center justify-center rounded-2xl border border-teal-200 bg-teal-50 px-4 py-2 text-sm font-semibold text-teal-800 transition hover:bg-teal-100">Gallery</a>
                                                    <a href="{{ route('featured') }}" class="w-full rounded-2xl border border-violet-200 bg-violet-50 px-4 py-2 text-sm font-semibold text-violet-800 transition hover:bg-violet-100">Premium</a>
                                                </div>
                                                <a href="{{ route('profiles.switch', $profile) }}" class="inline-flex h-full items-center justify-center rounded-2xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">Details</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    @else
                        <div class="rounded-[28px] border border-dashed border-gray-200 bg-white p-10 text-center">
                            <h2 class="text-xl font-semibold text-gray-900">No profiles yet</h2>
                            <p class="mt-2 text-sm text-gray-500">You don’t have any provider profiles yet. Create your first profile from the dashboard to get started.</p>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
