function videoGalleryModal(config = {}) {
    return {
        open: false,
        videos: Array.isArray(config.videos) ? config.videos : [],
        currentIdx: 0,
        gridView: false,
        slideshow: false,
        slideTimer: null,
        zoom: false,

        init() {
            document.querySelectorAll('.video-gallery-thumb').forEach((el, idx) => {
                el.addEventListener('click', () => {
                    this.openAt(idx);
                });
            });
        },

        openAt(idx) {
            if (!this.videos.length) return;

            this.open = true;
            this.currentIdx = idx;
            this.zoom = false;
            document.body.classList.add('overflow-hidden');

            this.$nextTick(() => {
                this.scrollGridToCurrent();
            });
        },

        close() {
            this.open = false;
            this.gridView = false;
            this.zoom = false;
            this.stopSlideshow();
            this.pauseAllVideos();
            document.body.classList.remove('overflow-hidden');
        },

        next() {
            if (!this.videos.length) return;

            this.pauseAllVideos();

            if (this.currentIdx < this.videos.length - 1) {
                this.currentIdx++;
            } else {
                this.currentIdx = 0;
            }

            this.scrollGridToCurrent();
        },

        prev() {
            if (!this.videos.length) return;

            this.pauseAllVideos();

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
                    imgs[this.currentIdx].scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest',
                        inline: 'nearest'
                    });
                }
            });
        },

        toggleGrid() {
            this.gridView = !this.gridView;

            if (this.gridView) {
                this.$nextTick(() => {
                    this.scrollGridToCurrent();
                });
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
            if (!this.videos.length || this.videos.length < 2) return;

            this.stopSlideshow();
            this.slideshow = true;

            this.slideTimer = setInterval(() => {
                this.next();
            }, 3000);
        },

        stopSlideshow() {
            this.slideshow = false;

            if (this.slideTimer) {
                clearInterval(this.slideTimer);
                this.slideTimer = null;
            }
        },

        pauseAllVideos() {
            this.$nextTick(() => {
                document.querySelectorAll('video').forEach((video) => {
                    try {
                        video.pause();
                    } catch (e) {}
                });
            });
        },

        toggleZoom() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen?.();
            } else {
                document.exitFullscreen?.();
            }
        }
    };
}
