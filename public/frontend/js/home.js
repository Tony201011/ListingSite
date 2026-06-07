document.addEventListener('DOMContentLoaded', function () {
    // Minimum movement (px) used only when slide measurements are unavailable.
    const MIN_FEATURED_SCROLL_AMOUNT = 100;

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

    document.querySelectorAll('[data-featured-slider]').forEach(function (slider) {
        const track = slider.querySelector('[data-slider-track]');
        const prevButton = slider.querySelector('[data-slider-prev]');
        const nextButton = slider.querySelector('[data-slider-next]');
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        // 1px tolerance avoids boundary flicker from sub-pixel scroll rounding.
        const scrollEdgeTolerance = 1;
        let resizeObserver = null;
        let resizeFallbackAttached = false;

        if (!track || !prevButton || !nextButton) {
            return;
        }

        const getScrollAmount = function () {
            const firstSlide = track.querySelector('.featured-slider-slide');

            if (!firstSlide) {
                return Math.max(track.clientWidth, MIN_FEATURED_SCROLL_AMOUNT);
            }

            const trackStyles = window.getComputedStyle(track);
            const gap = parseFloat(trackStyles.columnGap || trackStyles.gap || '0') || 0;
            const slideWidth = firstSlide.getBoundingClientRect().width;
            const singleSlideSpan = slideWidth + gap;

            if (singleSlideSpan < 1) {
                console.warn('Featured slider has invalid slide span; using minimal fallback.');
                return Math.max(track.clientWidth, MIN_FEATURED_SCROLL_AMOUNT);
            }

            const slidesPerView = Math.max(1, Math.floor(track.clientWidth / singleSlideSpan));

            return slidesPerView * singleSlideSpan;
        };

        const updateButtons = function () {
            const maxScrollLeft = Math.max(track.scrollWidth - track.clientWidth, 0);
            const currentScrollLeft = Math.round(track.scrollLeft);

            prevButton.disabled = currentScrollLeft <= scrollEdgeTolerance;
            nextButton.disabled = currentScrollLeft >= Math.round(maxScrollLeft) - scrollEdgeTolerance;
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

        toSeoSearchName(value) {
            return String(value || '')
                .trim()
                .toLowerCase()
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
        },

        submitForm(form) {
            if (!form) {
                return;
            }

            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
                return;
            }

            form.submit();
        },

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
            this.$nextTick(() => this.submitForm(form));
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
                this.$nextTick(() => this.submitForm(event.target.closest('form')));
                return;
            }
            this.closeSuggestions();
            this.submitForm(event.target.closest('form'));
        },

        handleFormSubmit(event) {
            if (this.highlightedIndex >= 0 && this.suggestions[this.highlightedIndex]) {
                event.preventDefault();
                // Pass false so selectSuggestion doesn't also call form.submit();
                // we submit explicitly below so the caller controls timing.
                this.selectSuggestion(this.suggestions[this.highlightedIndex], event, false);
                // Wait for Alpine.js to apply the updated :name binding before submitting.
                this.$nextTick(() => this.submitForm(event.target.closest('form')));
                return;
            }

            if (this.searchMode === 'username') {
                if (this.term.trim() === '') {
                    event.preventDefault();
                    this.closeSuggestions();
                }
            }

            this.closeSuggestions();
        }
    };
}

function favouriteBookmark(config) {
    return {
        viewMode: config.viewMode || 'grid',
        favourites: Array.isArray(config.favourites) ? config.favourites.map(String) : [],

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
                    console.warn('Favourite toggle request failed with HTTP status:', response.status, response.statusText);
                    return null;
                }

                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    console.warn('Favourite toggle returned non-JSON content type:', contentType || '(empty)');
                    return null;
                }

                const data = await response.json();

                if (typeof data.active !== 'boolean') {
                    console.warn('Unexpected favourite toggle response payload:', data);
                    return null;
                }

                return data.active;
            } catch (error) {
                console.error('Favourite toggle request failed:', error);
                return null;
            }
        },

        isFavourite(slug) {
            slug = this.normalize(slug);
            return this.favourites.includes(slug);
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
    };
}

function featuredCarousel(total) {
    return {
        page: 0,
        pageSize: 1,
        total: total,
        isDragging: false,
        startX: 0,
        currentX: 0,
        dragOffset: 0,
        _slideW: 0,
        _cardW: 300,
        get pages() { return Math.max(1, Math.ceil(this.total / this.pageSize)); },
        computeDimensions() {
            const track = this.$refs.track;
            if (!track) return;
            const container = track.parentElement;
            if (!container) return;
            const containerW = container.clientWidth;
            if (containerW === 0) {
                window.requestAnimationFrame(() => this.computeDimensions());
                return;
            }
            const gap = parseFloat(getComputedStyle(track).gap) || 16;
            const containerStyle = getComputedStyle(container);
            const paddingLeft = parseFloat(containerStyle.paddingLeft) || 0;
            const paddingRight = parseFloat(containerStyle.paddingRight) || 0;
            const usableW = containerW - paddingLeft - paddingRight;
            this._cardW = (usableW - (this.pageSize - 1) * gap) / this.pageSize;
            this._slideW = this._cardW + gap;
        },
        get translateX() {
            return -(this.page * this.pageSize * this._slideW) + this.dragOffset;
        },
        init() {
            this.updatePageSize();
            this.computeDimensions();
            this.$nextTick(() => {
                window.requestAnimationFrame(() => this.computeDimensions());
                if (window.ResizeObserver) {
                    const container = this.$refs.track && this.$refs.track.parentElement;
                    if (container) {
                        const ro = new ResizeObserver(() => {
                            window.requestAnimationFrame(() => this.computeDimensions());
                        });
                        ro.observe(container);
                        window.addEventListener('pagehide', () => ro.disconnect(), { once: true });
                    }
                }
            });
        },
        updatePageSize() {
            this.pageSize = window.innerWidth >= 1024 ? 4 : window.innerWidth >= 640 ? 2 : 1;
            if (this.page > this.pages - 1) {
                this.page = this.pages - 1;
            }
            this.$nextTick(() => {
                window.requestAnimationFrame(() => this.computeDimensions());
            });
        },
        prev() { if (this.page > 0) this.page--; },
        next() { if (this.page < this.pages - 1) this.page++; },
        startDrag(event) {
            if (this._slideW === 0) this.computeDimensions();
            this.isDragging = true;
            this.startX = event.type === 'mousedown' ? event.clientX : event.touches[0].clientX;
            this.currentX = this.startX;
            this.dragOffset = 0;
        },
        drag(event) {
            if (!this.isDragging) return;
            event.preventDefault();
            this.currentX = event.type === 'mousemove' ? event.clientX : event.touches[0].clientX;
            this.dragOffset = this.currentX - this.startX;
        },
        endDrag() {
            if (!this.isDragging) return;
            this.isDragging = false;
            const threshold = this._slideW > 0 ? this._slideW / 4 : 50;
            if (this.dragOffset > threshold && this.page > 0) {
                this.page--;
            } else if (this.dragOffset < -threshold && this.page < this.pages - 1) {
                this.page++;
            }
            this.dragOffset = 0;
        },
    };
}
