@extends('layouts.frontend')

@section('title', $profile['name'] . ' Profile')

@section('content')
<div class="min-h-screen bg-gray-100 text-gray-800">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-4 flex items-center gap-2 text-xs text-gray-500">
            <a href="{{ url('/') }}" class="hover:text-gray-700">Home</a>
            <span>›</span>
            <span>Listings</span>
            <span>›</span>
            <span class="text-gray-700">{{ $profile['name'] }}</span>
        </div>

        <div class="grid gap-6">
            <div class="space-y-6">
                <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
                    <div class="grid gap-5 sm:grid-cols-[160px_minmax(0,1fr)]">
                        <img src="{{ $profile['images'][0] ?? $profile['image'] }}" alt="{{ $profile['name'] }}" class="h-44 w-full rounded-xl object-cover sm:h-48">
                        <div>
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <h1 class="text-2xl font-bold text-gray-900">{{ $profile['name'] }}</h1>
                            </div>
                            <p class="text-sm font-semibold text-pink-600">Independent Escort • {{ $profile['age'] }} years</p>
                            <div class="mt-2 flex flex-wrap gap-2 text-xs text-gray-500">
                                <span class="rounded-full bg-gray-100 px-2 py-1">{{ $profile['service_1'] }}</span>
                                <span class="rounded-full bg-gray-100 px-2 py-1">{{ $profile['service_2'] }}</span>
                                <span class="rounded-full bg-gray-100 px-2 py-1">{{ $profile['city'] }}</span>
                            </div>

                            <p class="mt-4 text-sm leading-6 text-gray-600">{{ $profile['description'] }}</p>

                            <div class="mt-4 grid gap-2 text-sm text-gray-600 sm:grid-cols-2">
                                <p><span class="font-semibold text-gray-800">Rate:</span> {{ $profile['rate'] }}</p>
                                <p><span class="font-semibold text-gray-800">Height:</span> {{ $profile['height'] }}</p>
                                <p><span class="font-semibold text-gray-800">Status:</span> {{ $profile['active'] ? 'Online now' : 'Offline' }}</p>
                                <p><span class="font-semibold text-gray-800">Updated:</span> {{ $profile['date'] }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
                    <h2 class="mb-4 text-2xl font-bold text-gray-900">Photo Gallery</h2>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach(($profile['images'] ?? [$profile['image']]) as $mediaImage)
                            <img src="{{ $mediaImage }}" alt="{{ $profile['name'] }} image" class="h-52 w-full rounded-xl object-cover">
                        @endforeach
                    </div>
                </section>

                @if(!empty($profile['videos']))
                    <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
                        <h2 class="mb-4 text-2xl font-bold text-gray-900">Video Gallery</h2>
                        <div class="grid gap-4 sm:grid-cols-2">
                            @foreach($profile['videos'] as $mediaVideo)
                                <video class="h-56 w-full rounded-xl bg-black object-cover" controls preload="metadata">
                                    <source src="{{ $mediaVideo }}" type="video/mp4">
                                </video>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
                    <h2 class="mb-4 text-2xl font-bold text-gray-900">About</h2>
                    <p class="text-sm leading-7 text-gray-600">
                        Hi, I’m {{ $profile['name'] }}. I offer a discreet and premium companion experience focused on comfort, chemistry, and mutual respect. Whether you’re planning a social event, private dinner, or relaxed one-on-one time, I bring elegance, confidence, and warm conversation to every meeting.
                    </p>

                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div>
                            <h3 class="mb-2 text-lg font-semibold text-gray-900">Services</h3>
                            <ul class="space-y-1 text-sm text-gray-600">
                                <li>• {{ $profile['service_1'] }}</li>
                                <li>• {{ $profile['service_2'] }}</li>
                                <li>• GFE experience</li>
                                <li>• Dinner dates</li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="mb-2 text-lg font-semibold text-gray-900">Location</h3>
                            <ul class="space-y-1 text-sm text-gray-600">
                                <li>• {{ $profile['city'] }} central area</li>
                                <li>• Safe and discreet meetups</li>
                                <li>• Hotel visits available</li>
                                <li>• Travel by arrangement</li>
                            </ul>
                        </div>
                    </div>
                </section>

            </div>

        </div>

        <section class="mt-12">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-4xl font-bold text-gray-900">Nearby listings</h2>
                <a href="{{ url('/') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900">View all →</a>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($nearbyProfiles as $nearby)
                    <a href="{{ route('profile.show', $nearby['slug']) }}" class="overflow-hidden rounded-xl border border-gray-200 bg-white transition hover:-translate-y-0.5 hover:shadow-md">
                        <img src="{{ $nearby['image'] }}" alt="{{ $nearby['name'] }}" class="h-48 w-full object-cover">
                        <div class="p-3">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $nearby['name'] }}</h3>
                            <p class="text-xs text-gray-500">{{ $nearby['city'] }} • {{ $nearby['service_1'] }}</p>
                            <p class="mt-2 text-base font-bold text-gray-900">{{ $nearby['rate'] }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    </div>
</div>
@endsection
