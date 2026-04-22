document.addEventListener('alpine:init', () => {
    Alpine.store('videoControl', {
        pauseOthers(current) {
            document.querySelectorAll('video').forEach((video) => {
                if (video !== current) {
                    video.pause();
                }
            });
        }
    });

    Alpine.directive('pauseothers', (el) => {
        el.addEventListener('play', () => {
            Alpine.store('videoControl').pauseOthers(el);
        });
    });

    Alpine.data('profileShowPage', (config = {}) => ({
        favourites: config.favourites || [],
        reportUrl: config.reportUrl || '',
        profileId: config.profileId || null,

        reportModalOpen: false,
        reportSubmitting: false,
        reportError: '',
        reportSuccess: '',

        init() {
            this.initSmoothScroll();
            this.initLazyImages();
            this.initVideoAutoPause();
        },

        initSmoothScroll() {
            document.querySelectorAll('a.smooth-scroll[href^="#"]').forEach((anchor) => {
                anchor.addEventListener('click', (event) => {
                    const href = anchor.getAttribute('href') || '';
                    const targetId = href.slice(1);
                    const target = document.getElementById(targetId);

                    if (!target) return;

                    event.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                });
            });
        },

        initLazyImages() {
            const lazyImages = document.querySelectorAll('img.lazy-img');

            if (!lazyImages.length) return;

            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries, imgObserver) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) return;

                        const img = entry.target;
                        const markLoaded = () => img.classList.add('is-loaded');

                        if (img.complete) {
                            markLoaded();
                        } else {
                            img.addEventListener('load', markLoaded, { once: true });
                            img.addEventListener('error', markLoaded, { once: true });
                        }

                        imgObserver.unobserve(img);
                    });
                }, {
                    rootMargin: '150px'
                });

                lazyImages.forEach((img) => observer.observe(img));
            } else {
                lazyImages.forEach((img) => img.classList.add('is-loaded'));
            }
        },

        initVideoAutoPause() {
            document.addEventListener('play', (event) => {
                if (event.target.tagName !== 'VIDEO') return;

                document.querySelectorAll('video').forEach((video) => {
                    if (video !== event.target) {
                        video.pause();
                    }
                });
            }, true);
        },

        isFavourite(slug) {
            return this.favourites.includes(slug);
        },

        async toggleFavourite(slug) {
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

                const response = await fetch('/favourite/' + encodeURIComponent(slug), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) return;

                const data = await response.json();

                if (data.active) {
                    if (!this.favourites.includes(slug)) {
                        this.favourites.push(slug);
                    }
                } else {
                    this.favourites = this.favourites.filter((item) => item !== slug);
                }
            } catch (error) {
                console.error('Favourite toggle failed:', error);
            }
        },

        openReportModal() {
            this.reportError = '';
            this.reportSuccess = '';
            this.reportSubmitting = false;
            this.reportModalOpen = true;
            document.body.classList.add('overflow-hidden');
        },

        closeReportModal() {
            this.reportModalOpen = false;
            this.reportSubmitting = false;
            this.reportError = '';
            this.reportSuccess = '';
            document.body.classList.remove('overflow-hidden');
        },

        async submitReport(event) {
            const form = event?.target;

            if (!form) {
                this.reportError = 'Unable to submit report. Please refresh and try again.';
                return;
            }

            this.reportSubmitting = true;
            this.reportError = '';
            this.reportSuccess = '';

            const formData = new FormData(form);

            try {
                const response = await fetch(this.reportUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': formData.get('_token') || '',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });

                let data = {};
                const contentType = response.headers.get('content-type') || '';

                if (contentType.includes('application/json')) {
                    data = await response.json();
                } else {
                    const text = await response.text();
                    data = { message: text || 'Unexpected server response.' };
                }

                if (response.ok) {
                    this.reportSuccess = 'Thank you! Your report has been submitted and will be reviewed by our team.';
                    form.reset();

                    const hiddenInput = form.querySelector('[name="provider_profile_id"]');
                    if (hiddenInput) {
                        hiddenInput.value = this.profileId;
                    }

                    setTimeout(() => {
                        this.closeReportModal();
                    }, 2000);
                } else {
                    this.reportError = data.errors
                        ? Object.values(data.errors).flat().join(' ')
                        : (data.message || 'An error occurred. Please try again.');
                }
            } catch (error) {
                console.error('Report submission failed:', error);
                this.reportError = 'A network error occurred. Please try again.';
            } finally {
                this.reportSubmitting = false;
            }
        },
    }));

    Alpine.data('nearbySlider', (total = 0) => ({
        page: 0,
        total: total,
        itemsPerView: 1,
        itemWidth: 0,
        translateX: 0,

        init() {
            this.handleResize();

            this.$nextTick(() => {
                this.updateDimensions();
            });
        },

        get totalPages() {
            if (!this.total) return 0;
            return Math.ceil(this.total / this.itemsPerView);
        },

        get isFirst() {
            return this.page <= 0;
        },

        get isLast() {
            return this.page >= this.totalPages - 1;
        },

        getItemsPerView() {
            if (window.innerWidth >= 1024) return 3;
            if (window.innerWidth >= 768) return 2;
            return 1;
        },

        handleResize() {
            const oldItemsPerView = this.itemsPerView;
            this.itemsPerView = this.getItemsPerView();

            this.$nextTick(() => {
                this.updateDimensions();

                if (oldItemsPerView !== this.itemsPerView && this.page > this.totalPages - 1) {
                    this.page = Math.max(this.totalPages - 1, 0);
                    this.updateTranslate();
                }
            });
        },

        updateDimensions() {
            const viewport = this.$refs.viewport;
            if (!viewport) return;

            const viewportWidth = viewport.clientWidth;
            this.itemWidth = viewportWidth / this.itemsPerView;
            this.updateTranslate();
        },

        updateTranslate() {
            const viewport = this.$refs.viewport;
            if (!viewport) return;

            this.translateX = this.page * viewport.clientWidth;
        },

        prev() {
            if (this.isFirst) return;
            this.page--;
            this.updateTranslate();
        },

        next() {
            if (this.isLast) return;
            this.page++;
            this.updateTranslate();
        },

        goTo(index) {
            if (index < 0 || index >= this.totalPages) return;
            this.page = index;
            this.updateTranslate();
        }
    }));
});
