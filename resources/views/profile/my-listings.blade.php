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
                    <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500">
                        <span class="font-semibold text-gray-900">All Listings</span>
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">Online</span>
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">Expiring</span>
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">Expired</span>
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">Offline</span>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div class="relative w-full sm:w-64">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="text" class="w-full rounded-full border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-4 text-sm text-gray-700 focus:border-pink-500 focus:outline-none focus:ring-2 focus:ring-pink-100" placeholder="Search listings" disabled>
                        </div>
                        <button type="button" class="inline-flex items-center rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-pink-300 hover:text-pink-700">
                            Sort by: Upload (oldest first)
                        </button>
                    </div>
                </div>
            </div>

            <div class="space-y-5 p-5 sm:p-6">
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
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <h2 class="truncate text-2xl font-semibold text-gray-900">{{ $listing->title }}</h2>
                                            <p class="mt-2 text-sm text-gray-500">
                                                {{ $listing->category ?: 'Uncategorized' }} · {{ $listing->age ? $listing->age.' years' : 'Age not set' }} · {{ ucfirst($listing->website_type) }}
                                            </p>
                                        </div>
                                        <span class="inline-flex rounded-full border border-pink-100 bg-pink-50 px-3 py-1 text-xs font-semibold text-pink-700">
                                            {{ $listing->is_vip ? 'VIP' : 'Standard' }}
                                        </span>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500">
                                        <span class="inline-flex items-center gap-2 rounded-2xl border border-gray-200 bg-gray-50 px-3 py-2">
                                            <i class="fa-regular fa-heart text-pink-500"></i>
                                            {{ number_format($listing->audience_score, 2) }} score
                                        </span>
                                        <span class="inline-flex items-center gap-2 rounded-2xl border border-gray-200 bg-gray-50 px-3 py-2">
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
            </div>
        </div>
    </div>
</div>
@endsection
