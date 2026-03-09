<!-- Gallery Modal -->
<div x-data="galleryModal()" x-init="init()" x-show="open" class="fixed inset-0 z-50 bg-black bg-opacity-95 flex flex-col sm:flex-row"
    x-cloak
    x-effect="open ? document.body.classList.add('overflow-hidden') : document.body.classList.remove('overflow-hidden')">

    <!-- Overlay -->
    <div class="absolute inset-0" @click="close()"></div>

    <!-- TOP BAR -->
    <div class="absolute top-0 left-0 w-full flex items-center justify-between px-4 sm:px-6 py-2 sm:py-3 bg-[#222] z-50">

        <span class="text-white text-lg" x-text="(currentIdx + 1) + ' / ' + images.length"></span>

        <div class="flex items-center gap-8 text-gray-300 text-xl">

            <!-- Zoom -->
            <button @click="toggleZoom()"
                class="w-10 h-10 flex items-center justify-center bg-[#2b2b2b] rounded hover:bg-[#3a3a3a] transition">

                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24">

                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>

                </svg>

            </button>

            <!-- Play / Pause -->
            <button @click="toggleSlideshow()"
                class="w-10 h-10 flex items-center justify-center bg-[#2b2b2b] rounded hover:bg-[#3a3a3a] transition">

                <!-- PLAY -->
                <svg x-show="!slideshow" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white"
                    viewBox="0 0 24 24" fill="currentColor">

                    <polygon points="5,3 19,12 5,21"></polygon>

                </svg>

                <!-- PAUSE -->
                <svg x-show="slideshow" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white"
                    viewBox="0 0 24 24" fill="currentColor">

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

        <!-- MAIN IMAGE -->
        <div class="flex-1 flex items-center justify-center relative overflow-hidden mb-4 sm:mb-0">

            <!-- LEFT ARROW -->
            <button @click="prev()" class="absolute left-2 sm:left-6 text-white text-3xl sm:text-4xl hover:scale-125 transition z-40">
                ❮
            </button>

            <!-- SLIDER -->
            <div class="w-full h-full overflow-hidden flex items-center justify-center">

                <div class="flex transition-transform duration-500 ease-in-out will-change-transform"
                    :style="'transform: translateX(-' + currentIdx * 100 + '%)'">

                    <template x-for="(img,idx) in images" :key="idx">

                        <div class="min-w-full flex items-center justify-center">

                            <img :src="img" loading="lazy" @click="toggleZoom()"
                                :class="zoom ? 'scale-150 cursor-zoom-out' : 'scale-100 cursor-zoom-in'"
                                class="max-h-[80vh] object-contain transition duration-300" />

                        </div>

                    </template>

                </div>

            </div>

            <!-- RIGHT ARROW (when gridView is false) -->
            <template x-if="!gridView">
                <button @click="next()" class="absolute right-2 sm:right-6 text-white text-3xl sm:text-4xl hover:scale-125 transition z-40">
                    ❯
                </button>
            </template>

            <!-- RIGHT GRID PANEL -->
            <template x-if="gridView">
                <div class="gallery-modal-grid-panel w-full sm:w-96 bg-black bg-opacity-80 p-2 sm:p-4 border-t sm:border-t-0 sm:border-l border-gray-700 scrollbar-thin scrollbar-thumb-gray-500 scrollbar-track-gray-900 absolute sm:static right-0 top-auto bottom-0 sm:top-0 sm:bottom-auto max-h-32 sm:max-h-[80vh] flex sm:block overflow-x-auto sm:overflow-y-auto z-50">
                    <div class="flex sm:grid sm:grid-cols-2 gap-2 sm:gap-4 w-full">
                        <template x-for="(img,idx) in images" :key="idx">
                            <img :src="img" loading="lazy" @click="currentIdx = idx"
                                :class="currentIdx === idx ? 'border-4 border-pink-500' : 'border-2 border-transparent'"
                                class="h-20 w-20 sm:w-full sm:h-40 object-cover rounded cursor-pointer hover:scale-105 transition flex-shrink-0" />
                        </template>
                    </div>
                </div>
            </template>
            <!-- RIGHT ARROW (when gridView is true, after grid) -->
            <template x-if="gridView">
                <button @click="next()" class="absolute right-2 top-1/2 transform -translate-y-1/2 sm:top-auto sm:bottom-auto sm:right-[21rem] text-white text-3xl sm:text-4xl hover:scale-125 transition z-40">
                    ❯
                </button>
            </template>
<style>
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

        </div>




    </div>
</div>


<script>
    function galleryModal() {

        return {

            open: false,
            images: [],
            currentIdx: 0,

            zoom: false,
            gridView: false,
            slideshow: false,
            slideTimer: null,

            init() {

                this.images = Array.from(
                    document.querySelectorAll('.gallery-img-clickable')
                ).map(img => img.src);

                document.querySelectorAll('.gallery-img-clickable').forEach((img, idx) => {

                    img.addEventListener('click', () => {

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
                if (this.currentIdx < this.images.length - 1) {
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
                    this.currentIdx = this.images.length - 1;
                }
                this.scrollGridToCurrent();
            },

            scrollGridToCurrent() {
                if (!this.gridView) return;
                this.$nextTick(() => {
                    const grid = document.querySelector('.gallery-modal-grid-panel');
                    if (!grid) return;
                    const imgs = grid.querySelectorAll('img');
                    if (imgs[this.currentIdx]) {
                        imgs[this.currentIdx].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'nearest' });
                    }
                });
            },

            toggleZoom() {
                this.zoom = !this.zoom;
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

            }

        }
    }
</script>


<style>
    [x-cloak] {
        display: none !important;
    }
</style>
