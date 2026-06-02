@extends('layouts.frontend')

@section('title', 'Profile Details')

@section('content')
@php
    $location = collect([$profile->suburb, $profile->city?->name, $profile->state?->name])
        ->filter()
        ->unique()
        ->implode(', ');
    $profileSummary = $profile->introduction_line ?: $profile->description ?: $profile->profile_text;
@endphp
<div class="min-h-screen bg-gray-50 px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl">
        <div class="mb-6 flex flex-col gap-4 rounded-3xl border border-gray-200 bg-white p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">{{ $profile->name }}</h1>
                <p class="mt-2 text-sm text-gray-500">Profile details for the listing card shown in My Listings.</p>
            </div>
            <a href="{{ route('my-listings') }}" class="inline-flex items-center justify-center rounded-full bg-pink-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-pink-700">Back to Listings</a>
        </div>

        <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
            <div class="grid gap-6 p-6 lg:grid-cols-[320px_minmax(0,1fr)]">
                <div class="rounded-3xl bg-gray-100 p-4">
                    @if($profile->primaryProfileImage)
                        <img src="{{ $profile->primaryProfileImage->thumbnail_url }}" alt="{{ $profile->name }}" class="h-full w-full rounded-3xl object-cover">
                    @else
                        <div class="flex h-80 items-center justify-center rounded-3xl bg-gray-200 text-gray-500">No photo available</div>
                    @endif

                    <div class="mt-4 space-y-3 text-sm text-gray-600">
                        <div class="flex items-center justify-between rounded-2xl border border-gray-200 bg-white px-4 py-3">
                            <span>Status</span>
                            <span class="font-semibold text-gray-900">{{ $profile->isCurrentlyOnline() ? 'Online' : 'Offline' }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-2xl border border-gray-200 bg-white px-4 py-3">
                            <span>Featured</span>
                            <span class="font-semibold text-gray-900">{{ $profile->is_featured ? 'Yes' : 'No' }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-2xl border border-gray-200 bg-white px-4 py-3">
                            <span>Age</span>
                            <span class="font-semibold text-gray-900">{{ $profile->age ? $profile->age.' years' : 'Not set' }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-2xl border border-gray-200 bg-white px-4 py-3">
                            <span>Location</span>
                            <span class="text-right font-semibold text-gray-900">{{ $location ?: 'Not set' }}</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <section class="rounded-3xl border border-gray-200 bg-white p-6">
                        <h2 class="text-xl font-semibold text-gray-900">Profile information</h2>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4 sm:col-span-2">
                                <p class="text-xs uppercase tracking-wide text-gray-500">Summary</p>
                                <p class="mt-2 text-base leading-7 text-gray-900">{{ $profileSummary ?: 'No profile summary has been added yet.' }}</p>
                            </div>
                            <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500">Phone</p>
                                <p class="mt-2 text-base font-semibold text-gray-900">{{ $profile->phone ?: 'Not set' }}</p>
                            </div>
                            <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                                <p class="text-xs uppercase tracking-wide text-gray-500">Approval</p>
                                <p class="mt-2 text-base font-semibold text-gray-900">{{ ucfirst((string) ($profile->profile_status ?: 'pending')) }}</p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-gray-200 bg-white p-6">
                        <h2 class="text-xl font-semibold text-gray-900">Manage profile</h2>
                        <p class="mt-2 text-sm text-gray-500">Use these shortcuts to update the profile behind this listing card.</p>

                        <div class="mt-4 grid gap-3 sm:grid-cols-3">
                            <form action="{{ route('profiles.switch-edit', $profile) }}" method="POST" class="inline-flex w-full">
                                @csrf
                                <button type="submit" class="w-full rounded-2xl bg-pink-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-pink-700">
                                    Edit Profile
                                </button>
                            </form>

                            <a href="{{ route('photos') }}" class="inline-flex items-center justify-center rounded-2xl border border-amber-700 bg-amber-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-amber-700">
                                <i class="fa-solid fa-images mr-1"></i>
                                Manage Gallery
                            </a>

                            <a href="{{ route('featured') }}" class="inline-flex items-center justify-center rounded-2xl border border-amber-700 bg-amber-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-amber-700">
                                <i class="fa-solid fa-crown mr-1"></i>
                                Premium Features
                            </a>
                        </div>

                        <div class="mt-4">
                            <a href="{{ $profile->getEscortUrl() }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-full border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-gray-400 hover:text-gray-900">
                                View Public Profile
                                <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                            </a>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
