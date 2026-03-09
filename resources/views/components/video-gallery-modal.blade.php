<!-- Video Gallery Modal -->
<div x-data="videoGalleryModal()" x-init="init()" x-show="open" class="fixed inset-0 z-50 bg-black bg-opacity-95"
    x-cloak
    x-effect="open ? document.body.classList.add('overflow-hidden') : document.body.classList.remove('overflow-hidden')">

    <!-- Overlay -->
    <div class="absolute inset-0" @click="close()"></div>

    <!-- TOP BAR -->
    <div class="absolute top-0 left-0 w-full flex items-center justify-between px-4 sm:px-6 py-2 sm:py-3 bg-[#222] z-50">
        <span class="text-white text-lg" x-text="(currentIdx + 1) + ' / ' + videos.length"></span>
        <div class="flex items-center gap-8 text-gray-300 text-xl">
            <!-- Zoom (Fullscreen) -->
            <button @click="toggleZoom()"
                class="w-10 h-10 flex items-center justify-center bg-[#2b2b2b] rounded hover:bg-[#3a3a3a] transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="4" y="4" width="16" height="16" rx="2"/>
                    <path d="M8 8h8v8H8z"/>
                </svg>
            </button>
            <!-- Play / Pause (Slideshow) -->
            <button @click="toggleSlideshow()"
                class="w-10 h-10 flex items-center justify-center bg-[#2b2b2b] rounded hover:bg-[#3a3a3a] transition">
                <!-- PLAY -->
                <svg x-show="!slideshow" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="currentColor">
                    <polygon points="5,3 19,12 5,21"></polygon>
                </svg>
                <!-- PAUSE -->
                <svg x-show="slideshow" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="currentColor">
                    <rect x="6" y="4" width="4" height="16"></rect>
                    <rect x="14" y="4" width="4" height="16"></rect>
                </svg>
            </button>
            <!-- Grid -->
            <button @click="toggleGrid()" class="hover:text-white">▦</button>
            <!-- Close -->
            <button @click="close()" class="hover:text-white text-2xl">✕</button>
        </div>
    </div>

    <!-- MAIN AREA -->
    <div class="flex flex-col sm:flex-row w-full h-full pt-16">
        <!-- MAIN VIDEO AREA -->
        <div class="flex-1 flex items-center justify-center relative overflow-hidden mb-4 sm:mb-0">
            <!-- LEFT ARROW -->
            <button @click="prev()" class="absolute left-2 sm:left-6 text-white text-3xl sm:text-4xl hover:scale-125 transition z-40">
                ❮
            </button>
            <!-- SLIDER -->
            <div class="w-full h-full overflow-hidden flex items-center justify-center">
                <div class="flex transition-transform duration-500 ease-in-out will-change-transform"
                    :style="'transform: translateX(-' + currentIdx * 100 + '%)'">
                    <template x-for="(video,idx) in videos" :key="idx">
                        <div class="min-w-full flex items-center justify-center">
                            <template x-if="video.type === 'embed'">
                                <iframe :src="video.src" frameborder="0" allowfullscreen class="w-full max-h-[80vh] aspect-video"></iframe>
                            </template>
                            <template x-if="video.type === 'file'">
                                <video :src="video.src" controls x-pauseothers class="w-full max-h-[80vh] aspect-video"></video>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
            <!-- RIGHT ARROW -->
            <template x-if="!gridView">
                <button @click="next()" class="absolute right-2 sm:right-6 text-white text-3xl sm:text-4xl hover:scale-125 transition z-40">
                    ❯
                </button>
            </template>
        </div>
        <!-- GRID PANEL (right side on desktop) -->
        <template x-if="gridView">
            <div class="video-modal-grid-panel sm:w-96 w-full bg-black bg-opacity-80 p-2 sm:p-4 overflow-y-auto border-t sm:border-t-0 sm:border-l border-gray-700 scrollbar-thin scrollbar-thumb-gray-500 scrollbar-track-gray-900 max-h-40 sm:max-h-[80vh] absolute sm:static right-0 top-16 sm:top-0">
                <div class="grid grid-cols-2 gap-2 sm:gap-4">
                    <template x-for="(video,idx) in videos" :key="idx">
                        <img :src="video.thumb" loading="lazy" @click="currentIdx = idx"
                            :class="currentIdx === idx ? 'border-4 border-pink-500' : 'border-2 border-transparent'"
                            class="w-full h-24 sm:h-40 object-cover rounded cursor-pointer hover:scale-105 transition" />
                    </template>
                </div>
            </div>
        </template>
        <!-- RIGHT ARROW (when gridView is true, after grid) -->
        <template x-if="gridView">
            <button @click="next()" class="absolute right-2 sm:right-[21rem] text-white text-3xl sm:text-4xl hover:scale-125 transition z-40">
                ❯
            </button>
        </template>
        <style>
            .scrollbar-thin { scrollbar-width: thin; }
            .scrollbar-thumb-gray-500::-webkit-scrollbar-thumb { background: #6b7280; border-radius: 8px; }
            .scrollbar-track-gray-900::-webkit-scrollbar-track { background: #111827; }
            .scrollbar-thin::-webkit-scrollbar { width: 8px; }
        </style>
    </div>
</div>

<script>
function videoGalleryModal() {
    return {
        open: false,
        videos: [], // {type: 'embed'|'file', src: '', thumb: ''}
        currentIdx: 0,
        gridView: false,
        slideshow: false,
        slideTimer: null,
        zoom: false,
        init() {
            // Example: populate videos array. Replace with your own logic.
            this.videos = [
                { type: 'embed', src: 'https://www.youtube.com/embed/VIDEO_ID', thumb: '/path/to/thumb1.jpg' },
                { type: 'file', src: '/storage/videos/video1.mp4', thumb: '/path/to/thumb2.jpg' },
                // ...
            ];
            document.querySelectorAll('.video-gallery-thumb').forEach((el, idx) => {
                el.addEventListener('click', () => {
                    this.open = true;
                    this.currentIdx = idx;
                });
            });
        },
        close() {
            this.open = false;
            this.stopSlideshow();
            this.zoom = false;
        },
        next() {
            if (this.currentIdx < this.videos.length - 1) {
                this.currentIdx++;
            } else {
                this.currentIdx = 0;
            }
            this.scrollGridToCurrent();
        },
        prev() {
            if (this.currentIdx > 0) {
                this.currentIdx--;
            } else {
                this.currentIdx = this.videos.length - 1;
            }
            this.scrollGridToCurrent();
        },
        scrollGridToCurrent() {
            if (!this.gridView) return;
            this.$nextTick(() => {
                const grid = document.querySelector('.video-modal-grid-panel');
                if (!grid) return;
                const imgs = grid.querySelectorAll('img');
                if (imgs[this.currentIdx]) {
                    imgs[this.currentIdx].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'nearest' });
                }
            });
        },
        toggleGrid() {
            this.gridView = !this.gridView;
            if (this.gridView) {
                this.$nextTick(() => this.scrollGridToCurrent());
            }
        },
        toggleSlideshow() {
            if (this.slideshow) {
                this.stopSlideshow();
            } else {
                this.startSlideshow();
            }
        },
        startSlideshow() {
            this.slideshow = true;
            this.slideTimer = setInterval(() => {
                this.next();
            }, 3000);
        },
        stopSlideshow() {
            this.slideshow = false;
            clearInterval(this.slideTimer);
        },
        toggleZoom() {
            // For demo: toggle a class or use fullscreen API
            this.zoom = !this.zoom;
            // Optionally, implement fullscreen API here
        }
    }
}
</script>

<style>
    [x-cloak] { display: none !important; }
</style>
