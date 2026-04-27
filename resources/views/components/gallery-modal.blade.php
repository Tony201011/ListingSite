<!-- Gallery Modal -->
<div
    x-data="galleryModal()"
    x-show="open"
    x-cloak
    x-transition.opacity
    @keydown.window.escape="close()"
    @keydown.window.arrow-right="next()"
    @keydown.window.arrow-left="prev()"
    class="fixed inset-0 z-50 bg-black/95 flex flex-col sm:flex-row"
>
    <!-- Overlay -->
    <div class="absolute inset-0" @click="close()"></div>

    <!-- TOP BAR -->
    <div class="absolute top-0 left-0 w-full flex items-center justify-between px-4 sm:px-6 py-2 sm:py-3 bg-[#222] z-50">
        <span class="text-white text-lg" x-text="images.length ? ((currentIdx + 1) + ' / ' + images.length) : '0 / 0'"></span>

        <div class="flex items-center gap-4 sm:gap-8 text-gray-300 text-xl">
            <!-- Zoom -->
            <button
                type="button"
                @click="toggleZoom()"
                class="w-10 h-10 flex items-center justify-center bg-[#2b2b2b] rounded hover:bg-[#3a3a3a] transition"
                title="Zoom"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </button>

            <!-- Play / Pause -->
            <button
                type="button"
                @click="toggleSlideshow()"
                class="w-10 h-10 flex items-center justify-center bg-[#2b2b2b] rounded hover:bg-[#3a3a3a] transition"
                title="Play / Pause"
            >
                <svg
                    x-show="!slideshow"
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-5 h-5 text-white"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                >
                    <polygon points="5,3 19,12 5,21"></polygon>
                </svg>

                <svg
                    x-show="slideshow"
                    x-cloak
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-5 h-5 text-white"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                >
                    <rect x="6" y="4" width="4" height="16"></rect>
                    <rect x="14" y="4" width="4" height="16"></rect>
                </svg>
            </button>

            <!-- Grid -->
            <button
                type="button"
                @click="toggleGrid()"
                class="hover:text-white text-2xl"
                title="Toggle grid"
            >
                ▦
            </button>

            <!-- Close -->
            <button
                type="button"
                @click="close()"
                class="hover:text-white text-2xl"
                title="Close"
            >
                ✕
            </button>
        </div>
    </div>

    <!-- MAIN AREA -->
    <div class="flex flex-col sm:flex-row w-full h-full pt-16">
        <div class="flex-1 flex items-center justify-center relative overflow-hidden mb-4 sm:mb-0">
            <!-- LEFT ARROW -->
            <button
                type="button"
                @click="prev()"
                x-show="images.length > 1"
                class="absolute left-2 sm:left-6 text-white text-3xl sm:text-4xl hover:scale-125 transition z-40"
                title="Previous"
            >
                ❮
            </button>

            <!-- SLIDER -->
            <div class="w-full h-full overflow-hidden flex items-center justify-center">
                <div
                    class="flex transition-transform duration-500 ease-in-out will-change-transform h-full"
                    :style="'transform: translateX(-' + currentIdx * 100 + '%)'"
                >
                    <template x-for="(img, idx) in images" :key="idx">
                        <div class="min-w-full h-full flex items-center justify-center px-4">
                            <img
                                :src="img"
                                loading="lazy"
                                @click="toggleZoom()"
                                :class="zoom ? 'scale-150 cursor-zoom-out' : 'scale-100 cursor-zoom-in'"
                                class="max-h-[80vh] max-w-full object-contain transition duration-300"
                                alt="Gallery image"
                            />
                        </div>
                    </template>
                </div>
            </div>

            <!-- RIGHT ARROW -->
            <template x-if="!gridView && images.length > 1">
                <button
                    type="button"
                    @click="next()"
                    class="absolute right-2 sm:right-6 text-white text-3xl sm:text-4xl hover:scale-125 transition z-40"
                    title="Next"
                >
                    ❯
                </button>
            </template>

            <!-- RIGHT GRID PANEL -->
            <template x-if="gridView">
                <div class="gallery-modal-grid-panel w-full sm:w-96 bg-black/80 p-2 sm:p-4 border-t sm:border-t-0 sm:border-l border-gray-700 absolute sm:static right-0 bottom-0 sm:top-0 max-h-32 sm:max-h-[80vh] flex sm:block overflow-x-auto sm:overflow-y-auto z-50 scrollbar-thin scrollbar-thumb-gray-500 scrollbar-track-gray-900">
                    <div class="flex sm:grid sm:grid-cols-2 gap-2 sm:gap-4 w-full">
                        <template x-for="(img, idx) in images" :key="'thumb-' + idx">
                            <img
                                :src="img"
                                loading="lazy"
                                @click="currentIdx = idx; scrollGridToCurrent()"
                                :class="currentIdx === idx ? 'border-4 border-pink-500' : 'border-2 border-transparent'"
                                class="h-20 w-20 sm:w-full sm:h-40 object-cover rounded cursor-pointer hover:scale-105 transition flex-shrink-0"
                                alt="Gallery thumbnail"
                            />
                        </template>
                    </div>
                </div>
            </template>

            <!-- RIGHT ARROW WHEN GRID OPEN -->
            <template x-if="gridView && images.length > 1">
                <button
                    type="button"
                    @click="next()"
                    class="absolute right-2 top-1/2 -translate-y-1/2 sm:translate-y-0 sm:right-[21rem] text-white text-3xl sm:text-4xl hover:scale-125 transition z-40"
                    title="Next"
                >
                    ❯
                </button>
            </template>
        </div>
    </div>
</div>

<style>
    [x-cloak] {
        display: none !important;
    }

    .scrollbar-thin {
        scrollbar-width: thin;
    }

    .scrollbar-thin::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .scrollbar-thumb-gray-500::-webkit-scrollbar-thumb {
        background: #6b7280;
        border-radius: 8px;
    }

    .scrollbar-track-gray-900::-webkit-scrollbar-track {
        background: #111827;
    }
</style>
<script src="{{ asset('components/js/gallery-modal.js') }}"></script>
