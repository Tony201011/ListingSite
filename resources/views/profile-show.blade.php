@extends('layouts.frontend')

@section('title', $profile['name'] . ' Profile')

@section('content')
<div class="min-h-screen overflow-x-hidden bg-gray-50 text-gray-800">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-4 flex flex-wrap items-center gap-2 text-xs text-gray-500">
            <a href="{{ url('/') }}" class="hover:text-gray-700">Home</a>
            <span>›</span>
            <span>Listings</span>
            <span>›</span>
            <span class="text-gray-700">{{ $profile['name'] }}</span>
        </div>

        @php
            $primaryPhone = trim((string) ($profile['phone'] ?? $profile['whatsapp'] ?? '+61 451 442 992'));
            $phoneHref = preg_replace('/[^0-9+]/', '', $primaryPhone);
            $whatsAppHref = preg_replace('/[^0-9]/', '', $phoneHref);

            $priceList = collect($profile['price_list'] ?? [
                ['label' => '30 Minutes', 'price' => '$150'],
                ['label' => '45 Minutes', 'price' => '$180'],
                ['label' => '1 Hour', 'price' => '$200'],
                ['label' => '2 Hours', 'price' => '$380'],
            ])->values();

            $availabilityList = collect($profile['availability_list'] ?? [
                ['day' => 'Today', 'time' => 'Unavailable'],
                ['day' => 'Tomorrow', 'time' => '10:00 - 21:00'],
                ['day' => 'Sun', 'time' => '10:00 - 21:00'],
                ['day' => 'Mon', 'time' => '10:00 - 05:00'],
                ['day' => 'Tue', 'time' => '10:00 - 05:00'],
                ['day' => 'Wed', 'time' => '10:00 - 05:00'],
                ['day' => 'Thu', 'time' => 'Unavailable'],
            ])->values();

            $profileStats = [
                ['label' => 'Age group', 'value' => '25 - 29'],
                ['label' => 'Ethnicity', 'value' => 'Caucasian'],
                ['label' => 'Hair color', 'value' => 'Other'],
                ['label' => 'Hair length', 'value' => 'Short'],
                ['label' => 'Body type', 'value' => 'Athletic'],
                ['label' => 'Bust size', 'value' => 'Busty'],
                ['label' => 'Length', 'value' => 'Average (164cm - 176cm)'],
            ];

            $profileTags = [
                'nympho', 'bisexual', 'natural boobs', 'some tattoos', 'round bottom', 'fully shaved or waxed',
                'taned skin', 'lingerie', 'love conversations', 'shower facilities', 'published pornstar / model',
                'sensual experience', 'fantasy experiences', 'nuru'
            ];

            $contactForItems = [
                'Incalls only',
                'GFE bookings',
                'PSE or very naughty bookings',
                'Erotic body rubs',
            ];

            $sidebarFacts = [
                ['label' => 'Ethnicity', 'value' => 'Caucasian'],
                ['label' => 'Hair color', 'value' => 'Other'],
                ['label' => 'Eye color', 'value' => 'Brown'],
                ['label' => 'Body type', 'value' => 'Athletic'],
                ['label' => 'Height', 'value' => $profile['height'] ?? '5\'6"'],
                ['label' => 'Age', 'value' => ($profile['age'] ?? 25) . ' years'],
            ];
        @endphp

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px] xl:items-start">
            <div class="space-y-6">
                <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-6">
                    <div class="grid gap-4 sm:grid-cols-[160px_minmax(0,1fr)] sm:gap-5">
                        <img src="{{ $profile['images'][0] ?? $profile['image'] }}" alt="{{ $profile['name'] }}" class="h-44 w-full rounded-xl object-cover sm:h-48">
                        <div class="min-w-0">
                            <div class="mb-2 inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                AVAILABLE NOW • AVAILABLE TILL 8PM
                            </div>
                            <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm text-gray-500">{{ $profile['city'] }} Escorts</p>
                                    <h1 class="text-xl font-bold text-gray-900 sm:text-2xl">{{ strtoupper($profile['name']) }} <span class="font-medium text-gray-600">{{ strtoupper($profile['city']) }}</span></h1>
                                    <p class="mt-1 text-sm font-semibold uppercase tracking-wide text-pink-600">{{ $profile['service_1'] }} {{ $profile['service_2'] }} ❤️</p>
                                </div>

                                @if($primaryPhone !== '')
                                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-right">
                                        <p class="text-base font-bold text-gray-900"><i class="fa-solid fa-phone mr-1 text-sm"></i>{{ $primaryPhone }}</p>
                                        <p class="text-xs text-gray-500">I accept SMS messages</p>
                                    </div>
                                @endif
                            </div>

                            <p class="text-sm font-semibold text-pink-600">Independent Escort • {{ $profile['age'] }} years</p>
                            <div class="mt-2 flex flex-wrap gap-2 text-xs text-gray-500">
                                <span class="rounded-full bg-gray-100 px-2 py-1">{{ $profile['service_1'] }}</span>
                                <span class="rounded-full bg-gray-100 px-2 py-1">{{ $profile['service_2'] }}</span>
                                <span class="rounded-full bg-gray-100 px-2 py-1">{{ $profile['city'] }}</span>
                            </div>

                            <p class="mt-4 break-words text-sm leading-6 text-gray-600">{{ $profile['description'] }}</p>

                            <div class="mt-4 grid gap-2 text-sm text-gray-600 sm:grid-cols-2">
                                <p><span class="font-semibold text-gray-800">Rate:</span> {{ $profile['rate'] }}</p>
                                <p><span class="font-semibold text-gray-800">Height:</span> {{ $profile['height'] }}</p>
                                <p><span class="font-semibold text-gray-800">Status:</span> {{ $profile['active'] ? 'Online now' : 'Offline' }}</p>
                                <p><span class="font-semibold text-gray-800">Updated:</span> {{ $profile['date'] }}</p>
                            </div>

                            <div class="mt-4 grid grid-cols-1 gap-2 sm:flex sm:flex-wrap">
                                <a href="tel:{{ $phoneHref }}" class="inline-flex w-full items-center justify-center rounded-md bg-pink-600 px-3 py-2 text-xs font-semibold text-white hover:bg-pink-700 sm:w-auto">Call now</a>
                                <a href="https://wa.me/{{ $whatsAppHref }}" target="_blank" rel="noopener" class="inline-flex w-full items-center justify-center rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 sm:w-auto">WhatsApp</a>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-pink-200 bg-pink-50 p-5 shadow-sm sm:p-6">
                    <h2 class="mb-3 text-lg font-bold text-pink-700">Personal message</h2>
                    <div class="space-y-3 text-sm leading-6 text-gray-700">
                        <p><span class="font-semibold text-gray-900">Last updated:</span> Thu 12 February</p>
                        <p>I’m Working @ Lithe Massage.</p>
                        <p>For all bookings please <span class="font-semibold underline">TEXT</span> reception with your name, desired time, duration (30mins, 45mins, 60mins), and therapist you would like to see.</p>
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                    <h2 class="mb-4 text-xl font-bold text-gray-900 sm:text-2xl">Photo Gallery</h2>

                    @php
                        $galleryImages = $profile['images'] ?? [$profile['image']];
                    @endphp

                    <div x-data="gallerySlider(@js($galleryImages))" class="relative">
                        <button
                            type="button"
                            x-show="images.length > 1"
                            class="absolute left-2 top-1/2 z-10 -translate-y-1/2 rounded-full bg-white/90 p-2 text-gray-700 shadow hover:bg-white sm:px-3 sm:py-2"
                            @click="slidePrev"
                            aria-label="Previous images"
                        >
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>

                        <div
                            x-ref="track"
                            class="gallery-scroll flex gap-4 pb-2"
                            :class="images.length > 1 ? 'snap-x snap-mandatory overflow-x-auto scroll-smooth' : 'overflow-x-hidden'"
                        >
                            <template x-for="(image, index) in images" :key="index">
                                <button
                                    type="button"
                                    class="block min-w-full snap-start overflow-hidden rounded-xl sm:min-w-[calc(50%-0.5rem)] lg:min-w-[calc(33.333%-0.75rem)]"
                                    @click="open(index)"
                                >
                                    <img :src="image" alt="{{ $profile['name'] }} image" class="h-52 w-full object-cover">
                                </button>
                            </template>
                        </div>

                        <button
                            type="button"
                            x-show="images.length > 1"
                            class="absolute right-2 top-1/2 z-10 -translate-y-1/2 rounded-full bg-white/90 p-2 text-gray-700 shadow hover:bg-white sm:px-3 sm:py-2"
                            @click="slideNext"
                            aria-label="Next images"
                        >
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>

                        <template x-if="isOpen">
                            <div
                                @keydown.escape.window="close"
                                @keydown.arrow-right.window="isOpen && nextImage()"
                                @keydown.arrow-left.window="isOpen && prevImage()"
                                @click.self="close"
                                class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-3 sm:p-4"
                            >
                                <div x-show="images.length > 1" class="absolute left-1/2 top-3 -translate-x-1/2 rounded-full bg-white px-3 py-1 text-xs font-semibold text-gray-800 sm:top-4 sm:text-sm" x-text="`${currentIndex + 1} / ${images.length}`"></div>

                                <button
                                    type="button"
                                    x-show="images.length > 1"
                                    class="absolute left-2 top-1/2 -translate-y-1/2 rounded-full bg-white p-2 text-gray-700 sm:left-4 sm:px-3 sm:py-2"
                                    @click="prevImage"
                                    aria-label="Previous preview image"
                                >
                                    <i class="fa-solid fa-chevron-left"></i>
                                </button>

                                <button
                                    type="button"
                                    class="absolute right-2 top-2 rounded-full bg-white p-2 text-gray-700 sm:right-4 sm:top-4 sm:px-3 sm:py-2"
                                    @click="close"
                                    aria-label="Close image preview"
                                >
                                    <i class="fa-solid fa-xmark"></i>
                                </button>

                                <img :src="currentImage" alt="{{ $profile['name'] }} preview" class="max-h-[85vh] w-full max-w-[92vw] rounded-lg object-contain sm:max-h-[90vh] sm:max-w-[90vw]">

                                <button
                                    type="button"
                                    x-show="images.length > 1"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full bg-white p-2 text-gray-700 sm:right-4 sm:px-3 sm:py-2"
                                    @click="nextImage"
                                    aria-label="Next preview image"
                                >
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                </section>

                @if(!empty($profile['videos']))
                    <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                        <h2 class="mb-4 text-xl font-bold text-gray-900 sm:text-2xl">Video Gallery</h2>

                        <div x-data="videoGallerySlider(@js($profile['videos']))" class="relative">
                            <button
                                type="button"
                                x-show="videos.length > 1"
                                class="absolute left-2 top-1/2 z-10 -translate-y-1/2 rounded-full bg-white/90 p-2 text-gray-700 shadow hover:bg-white sm:px-3 sm:py-2"
                                @click="slidePrev"
                                aria-label="Previous videos"
                            >
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>

                            <div
                                x-ref="track"
                                class="gallery-scroll flex gap-4 pb-2"
                                :class="videos.length > 1 ? 'snap-x snap-mandatory overflow-x-auto scroll-smooth' : 'overflow-x-hidden'"
                            >
                                <template x-for="(video, index) in videos" :key="index">
                                    <button
                                        type="button"
                                        class="relative block min-w-full snap-start overflow-hidden rounded-xl bg-black sm:min-w-[calc(50%-0.5rem)] lg:min-w-[calc(33.333%-0.75rem)]"
                                        @click="open(index)"
                                    >
                                        <video class="h-56 w-full object-cover" preload="metadata" muted playsinline>
                                            <source :src="video" type="video/mp4">
                                        </video>
                                        <span class="absolute inset-0 flex items-center justify-center">
                                            <span class="rounded-full bg-black/60 px-4 py-3 text-white">
                                                <i class="fa-solid fa-play"></i>
                                            </span>
                                        </span>
                                    </button>
                                </template>
                            </div>

                            <button
                                type="button"
                                x-show="videos.length > 1"
                                class="absolute right-2 top-1/2 z-10 -translate-y-1/2 rounded-full bg-white/90 p-2 text-gray-700 shadow hover:bg-white sm:px-3 sm:py-2"
                                @click="slideNext"
                                aria-label="Next videos"
                            >
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>

                            <template x-if="isOpen">
                                <div
                                    @keydown.escape.window="close"
                                    @keydown.arrow-right.window="isOpen && nextVideo()"
                                    @keydown.arrow-left.window="isOpen && prevVideo()"
                                    @click.self="close"
                                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-3 sm:p-4"
                                >
                                    <div x-show="videos.length > 1" class="absolute left-1/2 top-3 -translate-x-1/2 rounded-full bg-white px-3 py-1 text-xs font-semibold text-gray-800 sm:top-4 sm:text-sm" x-text="`${currentIndex + 1} / ${videos.length}`"></div>

                                    <button
                                        type="button"
                                        x-show="videos.length > 1"
                                        class="absolute left-2 top-1/2 -translate-y-1/2 rounded-full bg-white p-2 text-gray-700 sm:left-4 sm:px-3 sm:py-2"
                                        @click="prevVideo"
                                        aria-label="Previous preview video"
                                    >
                                        <i class="fa-solid fa-chevron-left"></i>
                                    </button>

                                    <button
                                        type="button"
                                        class="absolute right-2 top-2 rounded-full bg-white p-2 text-gray-700 sm:right-4 sm:top-4 sm:px-3 sm:py-2"
                                        @click="close"
                                        aria-label="Close video preview"
                                    >
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>

                                    <video x-ref="modalVideo" class="max-h-[85vh] w-full max-w-[92vw] rounded-lg bg-black sm:max-h-[90vh] sm:max-w-[90vw]" controls autoplay playsinline preload="metadata" :src="currentVideo" @ended="handleEnded"></video>

                                    <button
                                        type="button"
                                        x-show="videos.length > 1"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full bg-white p-2 text-gray-700 sm:right-4 sm:px-3 sm:py-2"
                                        @click="nextVideo"
                                        aria-label="Next preview video"
                                    >
                                        <i class="fa-solid fa-chevron-right"></i>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </section>
                @endif

                <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                    <h2 class="mb-4 text-xl font-bold text-gray-900 sm:text-2xl">About</h2>
                    <p class="text-sm leading-7 text-gray-600">
                        Hi, I’m {{ $profile['name'] }}. I offer a discreet and premium companion experience focused on comfort, chemistry, and mutual respect. Whether you’re planning a social event, private dinner, or relaxed one-on-one time, I bring elegance, confidence, and warm conversation to every meeting.
                    </p>

                    @php
                        $serviceItems = collect([
                            $profile['service_1'] ?? null,
                            $profile['service_2'] ?? null,
                        ])
                            ->filter()
                            ->merge(collect($selectedCategoryNames ?? []))
                            ->map(fn ($item) => trim((string) $item))
                            ->filter()
                            ->unique()
                            ->values();

                        $groupedCategoryItems = collect($selectedCategoriesByGroup ?? [])
                            ->map(function ($group) {
                                return [
                                    'heading' => trim((string) ($group['heading'] ?? '')),
                                    'items' => collect($group['items'] ?? [])
                                        ->map(fn ($item) => trim((string) $item))
                                        ->filter()
                                        ->take(2)
                                        ->values()
                                        ->all(),
                                ];
                            })
                            ->filter(fn ($group) => $group['heading'] !== '' && !empty($group['items']))
                            ->values();
                    @endphp

                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div>
                            <h3 class="mb-2 text-lg font-semibold text-gray-900">Services</h3>
                            <ul class="space-y-1 text-sm text-gray-600">
                                @forelse($groupedCategoryItems as $group)
                                    <li>• <span class="font-semibold text-gray-900">{{ $group['heading'] }}:</span> {{ implode(', ', $group['items']) }}</li>
                                @empty
                                    @forelse($serviceItems as $serviceItem)
                                        <li>• {{ $serviceItem }}</li>
                                    @empty
                                        <li>• {{ $profile['service_1'] }}</li>
                                        <li>• {{ $profile['service_2'] }}</li>
                                    @endforelse
                                @endforelse
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

                <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                    <h2 class="mb-4 text-xl font-bold text-gray-900 sm:text-2xl">Price List</h2>

                    <div class="space-y-2 sm:hidden">
                        @foreach($priceList as $priceItem)
                            <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                                <span class="text-sm text-gray-700">{{ $priceItem['label'] }}</span>
                                <span class="text-sm font-semibold text-gray-900">{{ $priceItem['price'] }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="hidden overflow-x-auto rounded-lg border border-gray-200 sm:block">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Service</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Price</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach($priceList as $priceItem)
                                    <tr>
                                        <td class="px-4 py-3 text-gray-700">{{ $priceItem['label'] }}</td>
                                        <td class="px-4 py-3 font-semibold text-gray-900">{{ $priceItem['price'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                    <h2 class="mb-4 text-xl font-bold text-gray-900 sm:text-2xl">Availability</h2>

                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach($availabilityList as $availabilityItem)
                            <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                                <span class="text-sm font-semibold text-gray-900">{{ $availabilityItem['day'] }}</span>
                                <span class="text-sm text-gray-600">{{ $availabilityItem['time'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                    <h2 class="mb-4 text-xl font-bold text-gray-900 sm:text-2xl">My Stats</h2>

                    <div class="space-y-2 text-sm">
                        @foreach($profileStats as $stat)
                            <div class="flex items-start justify-between gap-3 border-b border-gray-100 pb-2">
                                <span class="font-semibold text-gray-700">{{ $stat['label'] }}</span>
                                <span class="text-gray-600">{{ $stat['value'] }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach($profileTags as $tag)
                            <span class="rounded-full bg-pink-100 px-2 py-1 text-xs font-semibold text-pink-700">{{ $tag }}</span>
                        @endforeach
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                    <div class="space-y-3 text-sm text-gray-700">
                        <p class="text-lg font-semibold text-gray-900"><i class="fa-solid fa-globe mr-2"></i>My website</p>
                        <a href="#" class="break-all text-pink-600 hover:underline">https://www.lithemassage.com/</a>
                    </div>

                    <div class="mt-6">
                        <h3 class="mb-2 text-lg font-bold text-gray-900">Contact me for</h3>
                        <ul class="space-y-2 text-sm text-gray-700">
                            @foreach($contactForItems as $item)
                                <li>» {{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="mt-6 border-t border-gray-100 pt-4 text-xs text-gray-500">
                        <p>Verified by Realbabes</p>
                        <p>I am on Realbabes since June 2025</p>
                    </div>
                </section>

            </div>

            <aside class="space-y-4 xl:sticky xl:top-6">
                <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                    <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-pink-600">My profile</h3>
                    <div class="space-y-2 text-sm">
                        @foreach($sidebarFacts as $fact)
                            <div class="flex items-start justify-between gap-2">
                                <span class="text-gray-500">{{ $fact['label'] }}</span>
                                <span class="font-medium text-gray-900">{{ $fact['value'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>
            </aside>

        </div>

        <section class="mt-12 overflow-hidden">
            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-2xl font-bold text-gray-900 sm:text-4xl">Nearby listings</h2>
                <a href="{{ url('/') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900">View all →</a>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($nearbyProfiles as $nearby)
                    <a href="{{ route('profile.show', array_merge(['slug' => $nearby['slug']], request()->query())) }}" class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
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

@push('styles')
<style>
    .gallery-scroll {
        -webkit-overflow-scrolling: touch;
        overscroll-behavior-x: contain;
        scrollbar-width: thin;
    }

    .gallery-scroll::-webkit-scrollbar {
        height: 8px;
    }

    .gallery-scroll::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 9999px;
    }
</style>
@endpush

@push('scripts')
<script>
    function gallerySlider(images) {
        return {
            images: images || [],
            isOpen: false,
            currentIndex: 0,
            get currentImage() {
                return this.images[this.currentIndex] || '';
            },
            open(index) {
                this.currentIndex = index;
                this.isOpen = true;
                document.body.classList.add('overflow-hidden');
            },
            close() {
                this.isOpen = false;
                document.body.classList.remove('overflow-hidden');
            },
            nextImage() {
                if (!this.images.length) {
                    return;
                }

                this.currentIndex = (this.currentIndex + 1) % this.images.length;
            },
            prevImage() {
                if (!this.images.length) {
                    return;
                }

                this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
            },
            slideNext() {
                const track = this.$refs.track;
                if (!track) {
                    return;
                }

                track.scrollBy({
                    left: Math.max(track.clientWidth * 0.9, 280),
                    behavior: 'smooth'
                });
            },
            slidePrev() {
                const track = this.$refs.track;
                if (!track) {
                    return;
                }

                track.scrollBy({
                    left: -Math.max(track.clientWidth * 0.9, 280),
                    behavior: 'smooth'
                });
            }
        };
    }

    function videoGallerySlider(videos) {
        return {
            videos: videos || [],
            isOpen: false,
            currentIndex: 0,
            get currentVideo() {
                return this.videos[this.currentIndex] || '';
            },
            open(index) {
                this.currentIndex = index;
                this.isOpen = true;
                document.body.classList.add('overflow-hidden');
            },
            close() {
                this.isOpen = false;
                document.body.classList.remove('overflow-hidden');

                const modalVideo = this.$refs.modalVideo;
                if (modalVideo) {
                    modalVideo.pause();
                    modalVideo.currentTime = 0;
                }
            },
            nextVideo() {
                if (!this.videos.length) {
                    return;
                }

                this.currentIndex = (this.currentIndex + 1) % this.videos.length;
                this.playCurrentVideo();
            },
            prevVideo() {
                if (!this.videos.length) {
                    return;
                }

                this.currentIndex = (this.currentIndex - 1 + this.videos.length) % this.videos.length;
                this.playCurrentVideo();
            },
            handleEnded() {
                if (this.videos.length > 1) {
                    this.nextVideo();
                }
            },
            playCurrentVideo() {
                this.$nextTick(() => {
                    const modalVideo = this.$refs.modalVideo;
                    if (!modalVideo) {
                        return;
                    }

                    modalVideo.currentTime = 0;
                    const playPromise = modalVideo.play();
                    if (playPromise && typeof playPromise.catch === 'function') {
                        playPromise.catch(() => {});
                    }
                });
            },
            slideNext() {
                const track = this.$refs.track;
                if (!track) {
                    return;
                }

                track.scrollBy({
                    left: Math.max(track.clientWidth * 0.9, 280),
                    behavior: 'smooth'
                });
            },
            slidePrev() {
                const track = this.$refs.track;
                if (!track) {
                    return;
                }

                track.scrollBy({
                    left: -Math.max(track.clientWidth * 0.9, 280),
                    behavior: 'smooth'
                });
            }
        };
    }
</script>
@endpush
