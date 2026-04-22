document.addEventListener('alpine:init', () => {
    Alpine.store('videoControl', {
        pauseOthers(current) {
            document.querySelectorAll('video').forEach((video) => {
                if (video !== current) {
                    video.pause();
                }
            });
        },
    });

    Alpine.data('profilePage', (config = {}) => ({
        favourites: config.favourites || [],

        init() {
            this.initSmoothScroll();
            this.initLazyImages();
        },

        initSmoothScroll() {
            this.$nextTick(() => {
                document.querySelectorAll('a.smooth-scroll[href^="#"]').forEach((anchor) => {
                    if (anchor.dataset.smoothBound === 'true') return;

                    anchor.dataset.smoothBound = 'true';

                    anchor.addEventListener('click', (e) => {
                        const targetId = anchor.getAttribute('href').slice(1);
                        const target = document.getElementById(targetId);

                        if (target) {
                            e.preventDefault();
                            target.scrollIntoView({
                                behavior: 'smooth',
                                block: 'start',
                            });
                        }
                    });
                });
            });
        },

        initLazyImages() {
            this.$nextTick(() => {
                const lazyImages = document.querySelectorAll('img.lazy-img');

                if (!lazyImages.length) return;

                if ('IntersectionObserver' in window) {
                    const imgObserver = new IntersectionObserver((entries) => {
                        entries.forEach((entry) => {
                            if (!entry.isIntersecting) return;

                            const img = entry.target;

                            if (img.complete) {
                                img.classList.add('is-loaded');
                            } else {
                                img.addEventListener('load', () => {
                                    img.classList.add('is-loaded');
                                }, { once: true });

                                img.addEventListener('error', () => {
                                    img.classList.add('is-loaded');
                                }, { once: true });
                            }

                            imgObserver.unobserve(img);
                        });
                    }, { rootMargin: '150px' });

                    lazyImages.forEach((img) => imgObserver.observe(img));
                } else {
                    lazyImages.forEach((img) => img.classList.add('is-loaded'));
                }
            });
        },

        isFavourite(slug) {
            return this.favourites.includes(slug);
        },

        async toggleFavourite(slug) {
            try {
                const res = await fetch('/favourite/' + encodeURIComponent(slug), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json',
                    },
                });

                if (!res.ok) return;

                const data = await res.json();

                if (data.active) {
                    if (!this.favourites.includes(slug)) {
                        this.favourites.push(slug);
                    }
                } else {
                    this.favourites = this.favourites.filter((s) => s !== slug);
                }
            } catch (error) {
                console.error('Favourite toggle failed:', error);
            }
        },
    }));

    Alpine.data('videoCard', (url = '') => ({
        url,
        loading: false,
        playing: false,
        error: false,
        ready: false,
        timeoutId: null,

        init() {
            const video = this.$refs.video;
            if (!video) return;

            this.loading = video.readyState < 2;
            this.ready = video.readyState >= 1;
            this.playing = !video.paused && !video.ended;
            this.error = false;

            if (this.loading) {
                this.startSafetyTimeout();
            }
        },

        get showLoader() {
            return this.loading && !this.error;
        },

        get showPlayOverlay() {
            return !this.playing && !this.loading && !this.error && this.ready;
        },

        startSafetyTimeout() {
            this.clearSafetyTimeout();

            this.timeoutId = setTimeout(() => {
                if (!this.ready && !this.error) {
                    this.loading = false;
                }
            }, 4000);
        },

        clearSafetyTimeout() {
            if (this.timeoutId) {
                clearTimeout(this.timeoutId);
                this.timeoutId = null;
            }
        },

        onLoadStart() {
            this.loading = true;
            this.error = false;
            this.startSafetyTimeout();
        },

        onReady() {
            this.ready = true;
            this.loading = false;
            this.error = false;
            this.clearSafetyTimeout();
        },

        onPlaying() {
            this.ready = true;
            this.loading = false;
            this.playing = true;
            this.error = false;
            this.clearSafetyTimeout();

            if (this.$refs.video) {
                Alpine.store('videoControl').pauseOthers(this.$refs.video);
            }
        },

        onPause() {
            this.playing = false;
            this.loading = false;
        },

        onWaiting() {
            if (!this.error) {
                this.loading = true;
            }
        },

        onSuspend() {
            if (this.ready) {
                this.loading = false;
            }
        },

        onEnded() {
            this.playing = false;
            this.loading = false;
        },

        onError() {
            this.error = true;
            this.loading = false;
            this.playing = false;
            this.ready = false;
            this.clearSafetyTimeout();
        },
    }));

    Alpine.data('reportModal', (config = {}) => ({
        open: false,
        submitting: false,
        success: false,
        error: '',
        reportUrl: config.reportUrl || '',
        profileId: config.profileId || '',

        show() {
            this.open = true;
            this.success = false;
            this.error = '';
        },

        hide() {
            this.open = false;
            this.submitting = false;
            this.success = false;
            this.error = '';
        },

        async submit() {
            if (this.submitting) return;

            const form = this.$refs.form;
            const formData = new FormData(form);

            this.submitting = true;
            this.error = '';
            this.success = false;

            try {
                const response = await fetch(this.reportUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': formData.get('_token'),
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const data = await response.json();

                if (response.ok) {
                    this.success = true;
                    form.reset();

                    const hiddenInput = form.querySelector('[name="provider_profile_id"]');
                    if (hiddenInput) {
                        hiddenInput.value = this.profileId;
                    }

                    setTimeout(() => {
                        this.hide();
                    }, 3000);
                } else {
                    this.error = data.errors
                        ? Object.values(data.errors).flat().join(' ')
                        : (data.message || 'An error occurred. Please try again.');
                }
            } catch (e) {
                this.error = 'A network error occurred. Please try again.';
            } finally {
                this.submitting = false;
            }
        },
    }));
});
