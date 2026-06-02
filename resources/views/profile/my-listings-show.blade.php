@extends('layouts.frontend')

@section('title', 'Listing Details')

@section('content')
<div class="min-h-screen bg-gray-50 px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl">
        <div class="mb-6 flex flex-col gap-4 rounded-3xl border border-gray-200 bg-white p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">{{ $listing->title }}</h1>
                <p class="mt-2 text-sm text-gray-500">Detailed view for your provider listing. Use the buttons below to manage this listing.</p>
            </div>
            <a href="{{ route('my-listings') }}" class="inline-flex items-center justify-center rounded-full bg-pink-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-pink-700">Back to Listings</a>
        </div>

        <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
            <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)] p-6">
                <div class="rounded-3xl bg-gray-100 p-4">
                    @if($listing->thumbnail)
                        <img src="{{ url($listing->thumbnail) }}" alt="{{ $listing->title }}" class="h-full w-full rounded-3xl object-cover">
                    @else
                        <div class="flex h-80 items-center justify-center rounded-3xl bg-gray-200 text-gray-500">No photo available</div>
                    @endif

                    <div class="mt-4 space-y-3 text-sm text-gray-600">
                        <div class="flex items-center justify-between rounded-2xl bg-white px-4 py-3 border border-gray-200">
                            <span>Status</span>
                            <span class="font-semibold text-gray-900">{{ $listing->is_live ? 'Online' : 'Offline' }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-2xl bg-white px-4 py-3 border border-gray-200">
                            <span>Premium</span>
                            <span class="font-semibold text-gray-900">{{ $listing->is_vip ? 'VIP' : 'Standard' }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-2xl bg-white px-4 py-3 border border-gray-200">
                            <span>Active</span>
                            <span class="font-semibold text-gray-900">{{ $listing->is_active ? 'Yes' : 'No' }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-2xl bg-white px-4 py-3 border border-gray-200">
                            <span>Audience score</span>
                            <span class="font-semibold text-gray-900">{{ number_format($listing->audience_score, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <section class="rounded-3xl border border-gray-200 bg-white p-6">
                        <h2 class="text-xl font-semibold text-gray-900">Listing information</h2>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500">Category</p>
                                <p class="mt-2 text-base font-semibold text-gray-900">{{ $listing->category ?: 'Uncategorized' }}</p>
                            </div>
                            <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500">Age</p>
                                <p class="mt-2 text-base font-semibold text-gray-900">{{ $listing->age ? $listing->age . ' years' : 'Not set' }}</p>
                            </div>
                            <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500">Website type</p>
                                <p class="mt-2 text-base font-semibold text-gray-900">{{ ucfirst($listing->website_type) }}</p>
                            </div>
                            <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500">Created</p>
                                <p class="mt-2 text-base font-semibold text-gray-900">{{ $listing->created_at->format('d M Y') }}</p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-gray-200 bg-white p-6">
                        <h2 class="text-xl font-semibold text-gray-900">Manage listing</h2>
                        <p class="mt-2 text-sm text-gray-500">Use these quick actions to update the listing state.</p>

                        <div class="mt-4 grid gap-3 sm:grid-cols-3">
                            <form action="{{ route('my-listings.feature', $listing) }}" method="POST" class="inline-flex w-full">
                                @csrf
                                <input type="hidden" name="feature" value="top">
                                <button type="submit" class="w-full rounded-2xl bg-yellow-50 px-4 py-3 text-sm font-semibold text-yellow-800 transition hover:bg-yellow-100">Mark Online</button>
                            </form>
                            <form action="{{ route('my-listings.feature', $listing) }}" method="POST" class="inline-flex w-full">
                                @csrf
                                <input type="hidden" name="feature" value="premium">
                                <button type="submit" class="w-full rounded-2xl border border-amber-700 bg-amber-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-amber-700">
                                    <i class="fa-solid fa-crown mr-1"></i>
                                    Upgrade to Premium
                                </button>
                            </form>
                            <a href="{{ route('photos') }}" class="inline-flex items-center justify-center rounded-2xl border border-sky-800 bg-sky-700 px-4 py-3 text-sm font-semibold text-white transition hover:bg-sky-800">
                                <i class="fa-solid fa-images mr-1"></i>
                                Manage Gallery
                            </a>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
