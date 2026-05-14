document.addEventListener('DOMContentLoaded', function () {
    const scrollTopButton = document.getElementById('smooth-scroll-top');
    if (scrollTopButton) {
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
    }

    document.querySelectorAll('[data-spotlight-slider]').forEach(function (slider) {
        const track = slider.querySelector('[data-slider-track]');
        const prevButton = slider.parentElement?.querySelector('[data-slider-prev]');
        const nextButton = slider.parentElement?.querySelector('[data-slider-next]');
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        let resizeObserver = null;
        let resizeFallbackAttached = false;

        if (!track || !prevButton || !nextButton) {
            return;
        }

        const getScrollAmount = function () {
            const firstSlide = track.querySelector('.spotlight-slider-slide');

            if (!firstSlide) {
                return track.clientWidth;
            }

            const trackStyles = window.getComputedStyle(track);
            const gap = parseFloat(trackStyles.columnGap || trackStyles.gap || '0') || 0;
            const slideWidth = firstSlide.getBoundingClientRect().width;
            const singleSlideSpan = slideWidth + gap;

            if (singleSlideSpan <= 0) {
                return track.clientWidth;
            }

            const slidesPerView = Math.max(1, Math.floor((track.clientWidth + gap) / singleSlideSpan));

            return slidesPerView * singleSlideSpan;
        };

        const updateButtons = function () {
            const maxScrollLeft = Math.max(track.scrollWidth - track.clientWidth, 0);
            const currentScrollLeft = Math.round(track.scrollLeft);

            prevButton.disabled = currentScrollLeft <= 1;
            nextButton.disabled = currentScrollLeft >= Math.round(maxScrollLeft) - 1;
        };

        const scrollSlider = function (direction) {
            track.scrollBy({
                left: getScrollAmount() * direction,
                behavior: prefersReducedMotion ? 'auto' : 'smooth',
            });
        };

        prevButton.addEventListener('click', function () {
            scrollSlider(-1);
        });

        nextButton.addEventListener('click', function () {
            scrollSlider(1);
        });

        track.addEventListener('scroll', updateButtons, { passive: true });
        track.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                scrollSlider(-1);
            } else if (event.key === 'ArrowRight') {
                event.preventDefault();
                scrollSlider(1);
            }
        });
        if (typeof ResizeObserver === 'function') {
            resizeObserver = new ResizeObserver(updateButtons);
            resizeObserver.observe(track);
        } else {
            window.addEventListener('resize', updateButtons);
            resizeFallbackAttached = true;
        }

        window.addEventListener('pagehide', function () {
            resizeObserver?.disconnect();

            if (resizeFallbackAttached) {
                window.removeEventListener('resize', updateButtons);
            }
        }, { once: true });

        updateButtons();
    });
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

            if (!autoSubmit) {
                return;
            }

            const form = (event && (event.currentTarget || event.target)
                ? (event.currentTarget || event.target).closest('form')
                : null) || document.querySelector('form');
            if (!form) {
                return;
            }

            // Wait for Alpine.js to apply the updated :name binding before submitting,
            // so the correct field name (location or escort_name) is sent.
            this.$nextTick(() => form.submit());
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
                // Pass false so selectSuggestion doesn't also call form.submit();
                // we submit explicitly below so the caller controls timing.
                this.selectSuggestion(this.suggestions[this.highlightedIndex], event, false);
                // Wait for Alpine.js to apply the updated :name binding before submitting.
                this.$nextTick(() => event.target.closest('form')?.submit());
                return;
            }
            this.closeSuggestions();
            event.target.closest('form')?.submit();
        },

        handleFormSubmit(event) {
            if (this.highlightedIndex >= 0 && this.suggestions[this.highlightedIndex]) {
                event.preventDefault();
                // Pass false so selectSuggestion doesn't also call form.submit();
                // we submit explicitly below so the caller controls timing.
                this.selectSuggestion(this.suggestions[this.highlightedIndex], event, false);
                // Wait for Alpine.js to apply the updated :name binding before submitting.
                this.$nextTick(() => event.target.closest('form')?.submit());
                return;
            }
            this.closeSuggestions();
        }
    };
}

function favouriteBookmark(config) {
    return {
        viewMode: config.viewMode || 'grid',
        favourites: Array.isArray(config.favourites) ? config.favourites.map(String) : [],
        bookmarks: Array.isArray(config.bookmarks) ? config.bookmarks.map(String) : [],

        normalize(slug) {
            return String(slug || '').trim();
        },

        async requestToggle(url) {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    console.warn('Favourite/bookmark toggle request failed with HTTP status:', response.status, response.statusText);
                    return null;
                }

                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    console.warn('Favourite/bookmark toggle returned non-JSON content type:', contentType || '(empty)');
                    return null;
                }

                const data = await response.json();

                if (typeof data.active !== 'boolean') {
                    console.warn('Unexpected favourite/bookmark toggle response payload:', data);
                    return null;
                }

                return data.active;
            } catch (error) {
                console.error('Favourite/bookmark toggle request failed:', error);
                return null;
            }
        },

        isFavourite(slug) {
            slug = this.normalize(slug);
            return this.favourites.includes(slug);
        },

        isBookmark(slug) {
            slug = this.normalize(slug);
            return this.bookmarks.includes(slug);
        },

        async toggleFavourite(slug) {
            slug = this.normalize(slug);
            if (!slug) return;

            const active = await this.requestToggle('/favourite/' + encodeURIComponent(slug));
            if (active === null) return;

            if (active) {
                if (!this.favourites.includes(slug)) this.favourites.push(slug);
            } else {
                this.favourites = this.favourites.filter(s => s !== slug);
            }
        },

        async toggleBookmark(slug) {
            slug = this.normalize(slug);
            if (!slug) return;

            const active = await this.requestToggle('/bookmark/' + encodeURIComponent(slug));
            if (active === null) return;

            if (active) {
                if (!this.bookmarks.includes(slug)) this.bookmarks.push(slug);
            } else {
                this.bookmarks = this.bookmarks.filter(s => s !== slug);
            }
        },
    };
}
