@extends('layouts.frontend')

@section('title', $profile['name'] . ' Profile')

@section('content')
<div class="profile-page min-h-screen overflow-x-clip bg-gray-100 text-gray-800">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-4 flex flex-wrap items-center gap-2 text-xs text-gray-500">
            <a href="{{ url('/') }}" class="hover:text-gray-700">Home</a>
            <span>›</span>
            <span>Listings</span>
            <span>›</span>
            <span class="text-gray-700">{{ $profile['name'] }}</span>
        </div>

        <div class="grid gap-6">
            <div class="space-y-6">
                <section class="rounded-xl border border-gray-200 bg-white p-4 sm:p-6">
                    <div class="grid gap-4 sm:grid-cols-[160px_minmax(0,1fr)] sm:gap-5">
                        <img src="{{ $profile['images'][0] ?? $profile['image'] }}" alt="{{ $profile['name'] }}" class="h-44 w-full rounded-xl object-cover sm:h-48">
                        <div class="min-w-0">
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <h1 class="text-xl font-bold text-gray-900 sm:text-2xl">{{ $profile['name'] }}</h1>
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
                        </div>
                    </div>
                </section>

                <section class="overflow-hidden rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
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
                    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
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

                <section class="overflow-hidden rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
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

                        $locationCategoryItems = $groupedCategoryItems
                            ->filter(function ($group) {
                                $heading = strtolower((string) ($group['heading'] ?? ''));

                                return str($heading)->contains([
                                    'availability',
                                    'contact',
                                    'phone',
                                    'location',
                                    'time waster',
                                ]);
                            })
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
                                @if($locationCategoryItems->isNotEmpty())
                                    <li>• <span class="font-semibold text-gray-900">City:</span> {{ $profile['city'] }}</li>
                                    @foreach($locationCategoryItems as $group)
                                        <li>• <span class="font-semibold text-gray-900">{{ $group['heading'] }}:</span> {{ implode(', ', $group['items']) }}</li>
                                    @endforeach
                                @else
                                    <li>• {{ $profile['city'] }} central area</li>
                                    <li>• Safe and discreet meetups</li>
                                    <li>• Hotel visits available</li>
                                    <li>• Travel by arrangement</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </section>

            </div>

        </div>

        <section class="mt-12 overflow-hidden">
            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-2xl font-bold text-gray-900 sm:text-4xl">Nearby listings</h2>
                <a href="{{ url('/') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900">View all →</a>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($nearbyProfiles as $nearby)
                    <a href="{{ route('profile.show', array_merge(['slug' => $nearby['slug']], request()->query())) }}" class="overflow-hidden rounded-xl border border-gray-200 bg-white transition hover:-translate-y-0.5 hover:shadow-md">
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
    html,
    body {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }

    .profile-page {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }

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
