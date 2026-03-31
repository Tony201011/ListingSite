@extends('layouts.frontend')

@section('content')
<div
    x-data="videoGalleryModal({
        videos: @js($videos ?? [])
    })"
    class="min-h-screen"
>
    <!-- Video Gallery Modal -->
    <div
        x-show="open"
        x-cloak
        x-transition.opacity
        @keydown.window.escape="close()"
        @keydown.window.arrow-right="next()"
        @keydown.window.arrow-left="prev()"
        class="fixed inset-0 z-50 bg-black/95"
    >
        <!-- Overlay -->
        <div class="absolute inset-0" @click="close()"></div>

        <!-- TOP BAR -->
        <div class="absolute top-0 left-0 z-50 flex w-full items-center justify-between bg-[#222] px-4 py-2 sm:px-6 sm:py-3">
            <span
                class="text-lg text-white"
                x-text="videos.length ? ((currentIdx + 1) + ' / ' + videos.length) : '0 / 0'"
            ></span>

            <div class="flex items-center gap-4 text-xl text-gray-300 sm:gap-8">
                <!-- Fullscreen -->
                <button
                    type="button"
                    @click="toggleZoom()"
                    class="flex h-10 w-10 items-center justify-center rounded bg-[#2b2b2b] transition hover:bg-[#3a3a3a]"
                    aria-label="Toggle fullscreen"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="4" y="4" width="16" height="16" rx="2"/>
                        <path d="M8 8h8v8H8z"/>
                    </svg>
                </button>

                <!-- Play / Pause -->
                <button
                    type="button"
                    @click="toggleSlideshow()"
                    class="flex h-10 w-10 items-center justify-center rounded bg-[#2b2b2b] transition hover:bg-[#3a3a3a]"
                    aria-label="Toggle slideshow"
                >
                    <svg x-show="!slideshow" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="currentColor">
                        <polygon points="5,3 19,12 5,21"></polygon>
                    </svg>

                    <svg x-show="slideshow" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="currentColor">
                        <rect x="6" y="4" width="4" height="16"></rect>
                        <rect x="14" y="4" width="4" height="16"></rect>
                    </svg>
                </button>

                <!-- Grid -->
                <button
                    type="button"
                    @click="toggleGrid()"
                    class="hover:text-white"
                    aria-label="Toggle grid"
                >
                    ▦
                </button>

                <!-- Close -->
                <button
                    type="button"
                    @click="close()"
                    class="text-2xl hover:text-white"
                    aria-label="Close"
                >
                    ✕
                </button>
            </div>
        </div>

        <!-- MAIN AREA -->
        <div class="flex h-full w-full flex-col pt-16 sm:flex-row">
            <!-- MAIN VIDEO AREA -->
            <div class="relative flex flex-1 items-center justify-center overflow-hidden">
                <!-- LEFT ARROW -->
                <button
                    type="button"
                    @click="prev()"
                    x-show="videos.length > 1"
                    class="absolute left-2 z-40 text-3xl text-white transition hover:scale-125 sm:left-6 sm:text-4xl"
                    aria-label="Previous video"
                >
                    ❮
                </button>

                <!-- SLIDER -->
                <div class="flex h-full w-full items-center justify-center overflow-hidden">
                    <div
                        class="flex transition-transform duration-500 ease-in-out will-change-transform"
                        :style="'transform: translateX(-' + currentIdx * 100 + '%)'"
                    >
                        <template x-for="(video, idx) in videos" :key="idx">
                            <div class="flex min-w-full items-center justify-center">
                                <template x-if="video.type === 'embed'">
                                    <iframe
                                        :src="video.src"
                                        frameborder="0"
                                        allowfullscreen
                                        class="aspect-video w-full max-h-[80vh]"
                                    ></iframe>
                                </template>

                                <template x-if="video.type === 'file'">
                                    <video
                                        :src="video.src"
                                        controls
                                        class="aspect-video w-full max-h-[80vh]"
                                    ></video>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- RIGHT ARROW -->
                <template x-if="!gridView && videos.length > 1">
                    <button
                        type="button"
                        @click="next()"
                        class="absolute right-2 z-40 text-3xl text-white transition hover:scale-125 sm:right-6 sm:text-4xl"
                        aria-label="Next video"
                    >
                        ❯
                    </button>
                </template>
            </div>

            <!-- GRID PANEL -->
            <template x-if="gridView">
                <div class="video-modal-grid-panel absolute right-0 top-16 max-h-40 w-full overflow-y-auto border-t border-gray-700 bg-black/80 p-2 sm:static sm:top-0 sm:max-h-[80vh] sm:w-96 sm:border-l sm:border-t-0 sm:p-4">
                    <div class="grid grid-cols-2 gap-2 sm:gap-4">
                        <template x-for="(video, idx) in videos" :key="'thumb-' + idx">
                            <img
                                :src="video.thumb"
                                loading="lazy"
                                @click="currentIdx = idx; scrollGridToCurrent()"
                                :class="currentIdx === idx ? 'border-4 border-pink-500' : 'border-2 border-transparent'"
                                class="h-24 w-full cursor-pointer rounded object-cover transition hover:scale-105 sm:h-40"
                                alt="Video thumbnail"
                            >
                        </template>
                    </div>
                </div>
            </template>

            <!-- RIGHT ARROW WITH GRID -->
            <template x-if="gridView && videos.length > 1">
                <button
                    type="button"
                    @click="next()"
                    class="absolute right-2 z-40 text-3xl text-white transition hover:scale-125 sm:right-[21rem] sm:text-4xl"
                    aria-label="Next video"
                >
                    ❯
                </button>
            </template>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    [x-cloak] {
        display: none !important;
    }

    .scrollbar-thin {
        scrollbar-width: thin;
    }

    .scrollbar-thumb-gray-500::-webkit-scrollbar-thumb {
        background: #6b7280;
        border-radius: 8px;
    }

    .scrollbar-track-gray-900::-webkit-scrollbar-track {
        background: #111827;
    }

    .scrollbar-thin::-webkit-scrollbar {
        width: 8px;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('components/js/video-gallery.js') }}"></script>
@endpush
