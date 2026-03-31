window.galleryModal = function () {
    return {
        open: false,
        images: [],
        currentIdx: 0,
        zoom: false,
        gridView: false,
        slideshow: false,
        slideTimer: null,

        init() {
            const clickableImages = Array.from(document.querySelectorAll('.gallery-img-clickable'));

            this.images = clickableImages
                .map((img) => img.src)
                .filter(Boolean);

            clickableImages.forEach((img, idx) => {
                if (img.dataset.galleryBound === 'true') {
                    return;
                }

                img.addEventListener('click', () => {
                    this.openAt(idx);
                });

                img.dataset.galleryBound = 'true';
            });
        },

        openAt(idx) {
            if (!this.images.length) return;

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
            this.zoom = false;
            this.gridView = false;
            this.stopSlideshow();

            document.body.classList.remove('overflow-hidden');
        },

        next() {
            if (!this.images.length) return;

            if (this.currentIdx < this.images.length - 1) {
                this.currentIdx++;
            } else {
                this.currentIdx = 0;
            }

            this.zoom = false;
            this.scrollGridToCurrent();
        },

        prev() {
            if (!this.images.length) return;

            if (this.currentIdx > 0) {
                this.currentIdx--;
            } else {
                this.currentIdx = this.images.length - 1;
            }

            this.zoom = false;
            this.scrollGridToCurrent();
        },

        scrollGridToCurrent() {
            if (!this.gridView) return;

            this.$nextTick(() => {
                const grid = this.$root.querySelector('.gallery-modal-grid-panel');
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

        toggleZoom() {
            this.zoom = !this.zoom;
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
            if (!this.images.length || this.images.length < 2) return;

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
        }
    };
};
