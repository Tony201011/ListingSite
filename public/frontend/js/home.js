document.addEventListener('DOMContentLoaded', function () {
    const scrollTopButton = document.getElementById('smooth-scroll-top');
    if (!scrollTopButton) {
        return;
    }

    const toggleScrollTopButton = function () {
        const shouldShow = window.scrollY > 300;
        scrollTopButton.classList.toggle('opacity-0', !shouldShow);
        scrollTopButton.classList.toggle('pointer-events-none', !shouldShow);
    };

    window.addEventListener('scroll', toggleScrollTopButton, { passive: true });

    scrollTopButton.addEventListener('click', function () {
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        window.scrollTo({ top: 0, behavior: prefersReducedMotion ? 'auto' : 'smooth' });
    });

    toggleScrollTopButton();
});

function escortSearch(config) {
    return {
        searchMode: config.initialMode || 'suburb',
        term: config.initialTerm || '',
        suggestions: [],
        showSuggestions: false,
        highlightedIndex: -1,
        abortController: null,
        userLat: config.userLat || '',
        userLng: config.userLng || '',
        distance: config.distance ?? config.maxDistance,
        maxDistance: config.maxDistance || 500,
        locationEnabled: config.locationEnabled || false,
        distanceSearchEnabled: config.distanceSearchEnabled ?? true,
        geoError: '',

        requestLocation() {
            this.geoError = '';
            if (!navigator.geolocation) {
                this.geoError = 'Geolocation not supported.';
                return;
            }
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    this.userLat = pos.coords.latitude;
                    this.userLng = pos.coords.longitude;
                    this.locationEnabled = true;
                },
                (err) => {
                    if (err.code === 1) {
                        this.geoError = 'Location access denied. Please allow location in your browser settings and try again.';
                    } else if (err.code === 2) {
                        this.geoError = 'Location unavailable. Please check your device location settings.';
                    } else if (err.code === 3) {
                        this.geoError = 'Location request timed out. Please try again.';
                    } else {
                        this.geoError = 'Unable to get location. Please allow access.';
                    }
                },
                { timeout: 10000, maximumAge: 60000 }
            );
        },

        clearLocation() {
            this.userLat = '';
            this.userLng = '';
            this.locationEnabled = false;
        },

        fetchSuggestions() {
            const q = this.term.trim();
            if (q.length < 2) {
                this.closeSuggestions();
                return;
            }

            if (this.abortController) {
                this.abortController.abort();
            }
            this.abortController = new AbortController();

            const isSuburbMode = this.searchMode === 'suburb';
            const url = (isSuburbMode ? config.suburbSuggestionsUrl : config.suggestionsUrl)
                + '?q=' + encodeURIComponent(q);

            fetch(url, {
                signal: this.abortController.signal,
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            })
            .then(r => r.ok ? r.json() : Promise.resolve(isSuburbMode ? [] : { suggestions: [] }))
            .then(data => {
                if (isSuburbMode) {
                    this.suggestions = (Array.isArray(data) ? data : []).map(item => ({
                        type: 'suburb',
                        name: [item.suburb, item.state].filter(Boolean).join(', '),
                        label: item.postcode || '',
                        value: [item.suburb, item.state].filter(Boolean).join(', '),
                    }));
                } else {
                    this.suggestions = (data.suggestions || []).map(item => ({
                        type: 'profile',
                        name: item.name || '',
                        value: item.name || '',
                        slug: item.slug || '',
                        label: item.location || '',
                        age: item.age,
                    }));
                }
                this.showSuggestions = this.suggestions.length > 0;
                this.highlightedIndex = -1;
            })
            .catch(err => {
                if (err.name !== 'AbortError') {
                    this.closeSuggestions();
                }
            });
        },

        selectSuggestion(item, event, autoSubmit = true) {
            this.term = item.value || item.name || this.term;
            this.searchMode = item.type === 'suburb' ? 'suburb' : 'username';
            this.closeSuggestions();

            const form = (event && (event.currentTarget || event.target)
                ? (event.currentTarget || event.target).closest('form')
                : null) || document.querySelector('form');
            if (!form) {
                return;
            }
            const locationInput = form.querySelector('input[name="location"]');
            const escortNameInput = form.querySelector('input[name="escort_name"]');
            if (locationInput) locationInput.value = item.type === 'suburb' ? (item.value || item.name || '') : '';
            if (escortNameInput) escortNameInput.value = item.type !== 'suburb' ? (item.value || item.name || '') : '';

            if (autoSubmit) {
                form.submit();
            }
        },

        closeSuggestions() {
            this.showSuggestions = false;
            this.highlightedIndex = -1;
        },

        highlightNext() {
            if (!this.showSuggestions) return;
            this.highlightedIndex = Math.min(this.highlightedIndex + 1, this.suggestions.length - 1);
        },

        highlightPrev() {
            if (!this.showSuggestions) return;
            this.highlightedIndex = Math.max(this.highlightedIndex - 1, -1);
        },

        selectHighlighted(event) {
            if (this.highlightedIndex >= 0 && this.suggestions[this.highlightedIndex]) {
                this.selectSuggestion(this.suggestions[this.highlightedIndex], event, false);
                event.target.closest('form')?.submit();
                return;
            }
            this.closeSuggestions();
            event.target.closest('form')?.submit();
        },

        handleFormSubmit(event) {
            if (this.highlightedIndex >= 0 && this.suggestions[this.highlightedIndex]) {
                event.preventDefault();
                this.selectSuggestion(this.suggestions[this.highlightedIndex], event, false);
                event.target.closest('form')?.submit();
                return;
            }
            this.closeSuggestions();
        }
    };
}

function favouriteBookmark(config) {
    return {
        viewMode: config.viewMode || 'grid',
        favourites: config.favourites || [],
        bookmarks: config.bookmarks || [],

        isFavourite(slug) {
            return this.favourites.includes(slug);
        },

        isBookmark(slug) {
            return this.bookmarks.includes(slug);
        },

        async toggleFavourite(slug) {
            const res = await fetch('/favourite/' + encodeURIComponent(slug), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });
            if (!res.ok) return;
            const data = await res.json();
            if (data.active) {
                if (!this.favourites.includes(slug)) this.favourites.push(slug);
            } else {
                this.favourites = this.favourites.filter(s => s !== slug);
            }
        },

        async toggleBookmark(slug) {
            const res = await fetch('/bookmark/' + encodeURIComponent(slug), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });
            if (!res.ok) return;
            const data = await res.json();
            if (data.active) {
                if (!this.bookmarks.includes(slug)) this.bookmarks.push(slug);
            } else {
                this.bookmarks = this.bookmarks.filter(s => s !== slug);
            }
        },
    };
}

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
        favourites: Array.isArray(config.favourites) ? config.favourites : [],
        reportUrl: config.reportUrl || '',
        profileId: config.profileId || null,

        reportModalOpen: false,
        reportSubmitting: false,
        reportError: '',
        reportSuccess: '',

        init() {
            this.initSmoothScroll();
            this.initLazyImages();
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
            this.reportModalOpen = true;
            document.body.classList.add('overflow-hidden');
        },

        closeReportModal() {
            this.reportModalOpen = false;
            document.body.classList.remove('overflow-hidden');
            this.reportError = '';
            this.reportSuccess = '';
            this.reportSubmitting = false;
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

                let data = {};
                try {
                    data = await response.json();
                } catch (e) {
                    data = {};
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
                this.reportError = 'A network error occurred. Please try again.';
            } finally {
                this.reportSubmitting = false;
            }
        },
    }));

    Alpine.data('nearbySlider', (config = {}) => ({
        items: Array.isArray(config.items) ? config.items : [],
        page: 0,
        perPage: 1,

        init() {
            this.handleResize();
            window.addEventListener('resize', this.handleResize.bind(this), { passive: true });
        },

        handleResize() {
            const width = window.innerWidth;

            if (width >= 1280) {
                this.perPage = 4;
            } else if (width >= 1024) {
                this.perPage = 3;
            } else if (width >= 640) {
                this.perPage = 2;
            } else {
                this.perPage = 1;
            }

            if (this.page > this.totalPages - 1) {
                this.page = Math.max(0, this.totalPages - 1);
            }
        },

        get total() {
            return this.items.length;
        },

        get totalPages() {
            return Math.max(1, Math.ceil(this.total / this.perPage));
        },

        get isFirst() {
            return this.page <= 0;
        },

        get isLast() {
            return this.page >= this.totalPages - 1;
        },

        get startIndex() {
            return this.page * this.perPage;
        },

        get endIndex() {
            return this.startIndex + this.perPage;
        },

        get visibleItems() {
            return this.items.slice(this.startIndex, this.endIndex);
        },

        get gridClass() {
            if (this.perPage === 4) return 'grid-cols-1 sm:grid-cols-2 xl:grid-cols-4';
            if (this.perPage === 3) return 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3';
            if (this.perPage === 2) return 'grid-cols-1 sm:grid-cols-2';
            return 'grid-cols-1';
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
            if (index >= 0 && index < this.totalPages) {
                this.page = index;
            }
        }
    }));
});
