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

        closeReportModal() {
            this.reportModalOpen = false;
            this.reportSubmitting = false;
            this.reportError = '';
            this.reportSuccess = '';
        },

        async submitReport(event) {
            this.reportSubmitting = true;
            this.reportError = '';
            this.reportSuccess = '';

            const form = event.target;
            const formData = new FormData(form);

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
                    this.reportSuccess = 'Thank you! Your report has been submitted and will be reviewed by our team.';
                    form.reset();

                    const hiddenInput = form.querySelector('[name="provider_profile_id"]');
                    if (hiddenInput) {
                        hiddenInput.value = this.profileId;
                    }

                    setTimeout(() => {
                        this.closeReportModal();
                    }, 3000);
                } else {
                    this.reportError = data.errors
                        ? Object.values(data.errors).flat().join(' ')
                        : (data.message || 'An error occurred. Please try again.');
                }
            } catch (error) {
                this.reportError = 'A network error occurred. Please try again.';
            } finally {
                this.reportSubmitting = false;
            }
        },
    }));

    Alpine.data('nearbySlider', (total = 0) => ({
        page: 0,
        total: total,

        get isFirst() {
            return this.page <= 0;
        },

        get isLast() {
            return this.page >= this.total - 1;
        },

        prev() {
            if (!this.isFirst) {
                this.page--;
            }
        },

        next() {
            if (!this.isLast) {
                this.page++;
            }
        },

        goTo(index) {
            if (index >= 0 && index < this.total) {
                this.page = index;
            }
        }
    }));
});
