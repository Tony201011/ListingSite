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

    const setHiddenState = function (element, hidden) {
        if (!element) {
            return;
        }

        element.classList.toggle('hidden', hidden);
    };

    // Track cleanup refs so re-initialisation (after content refresh) doesn't leak listeners.
    let _listingsScrollHandler = null;
    let _listingsSentinelObserver = null;

    const cleanupListingsScroll = function () {
        if (_listingsScrollHandler) {
            window.removeEventListener('scroll', _listingsScrollHandler);
            _listingsScrollHandler = null;
        }
        if (_listingsSentinelObserver) {
            _listingsSentinelObserver.disconnect();
            _listingsSentinelObserver = null;
        }
    };

    const initListingsScroll = function () {
        cleanupListingsScroll();

        const container = document.getElementById('listings-content');
        if (!container) {
            return;
        }

        const grid = container.querySelector('[data-listings-grid]');
        const paginationContainer = container.querySelector('[data-listings-pagination]');
        const sentinel = container.querySelector('[data-listings-sentinel]');
        const loadingMessage = container.querySelector('[data-listings-loading]');
        const errorMessage = container.querySelector('[data-listings-error]');
        const endMessage = container.querySelector('[data-listings-end]');

        if (!grid || !sentinel) {
            return;
        }

        // Always reset transient states on init.
        setHiddenState(loadingMessage, true);
        setHiddenState(errorMessage, true);
        setHiddenState(endMessage, true);

        let loadingNextPage = false;

        // --- URL / pagination active-state tracking ---

        const updatePaginationActive = function (pageNum) {
            document.querySelectorAll('[data-page-link]').forEach(function (link) {
                const linkPage = parseInt(link.dataset.pageLink, 10);
                const isActive = linkPage === pageNum;

                if (isActive) {
                    link.classList.remove(
                        'border-pink-200', 'bg-white', 'text-gray-700',
                        'hover:border-pink-300', 'hover:bg-pink-50', 'hover:text-pink-600',
                    );
                    link.classList.add('border-pink-600', 'bg-pink-600', 'text-white', 'shadow-sm');
                    link.setAttribute('aria-current', 'page');
                } else {
                    link.classList.remove('border-pink-600', 'bg-pink-600', 'text-white', 'shadow-sm');
                    link.classList.add(
                        'border-pink-200', 'bg-white', 'text-gray-700',
                        'hover:border-pink-300', 'hover:bg-pink-50', 'hover:text-pink-600',
                    );
                    link.removeAttribute('aria-current');
                }
            });
        };

        const updateUrlFromScroll = function () {
            const sections = Array.from(container.querySelectorAll('[data-page-section]'));
            if (sections.length === 0) {
                return;
            }

            // The current page is the last section whose top edge has entered the viewport.
            let current = sections[0];
            for (let i = 0; i < sections.length; i++) {
                const top = sections[i].getBoundingClientRect().top;
                if (top <= 100) {
                    current = sections[i];
                }
            }

            const url = current.dataset.pageUrl;
            const page = parseInt(current.dataset.pageSection, 10);

            if (url) {
                try {
                    const target = new URL(url, window.location.origin);
                    const targetPath = target.pathname + target.search;
                    const currentPath = window.location.pathname + window.location.search;
                    if (targetPath !== currentPath) {
                        history.replaceState({ page: page }, '', url);
                    }
                } catch (_) { /* invalid URL, skip */ }
                updatePaginationActive(page);
            }
        };

        let scrollTicking = false;
        _listingsScrollHandler = function () {
            if (!scrollTicking) {
                requestAnimationFrame(function () {
                    updateUrlFromScroll();
                    scrollTicking = false;
                });
                scrollTicking = true;
            }
        };
        window.addEventListener('scroll', _listingsScrollHandler, { passive: true });

        // Sync initial state without waiting for a scroll event.
        updateUrlFromScroll();

        // --- Infinite scroll ---

        if (!('IntersectionObserver' in window)) {
            // Older browsers: keep link-based pagination as-is.
            return;
        }

        const observeSentinel = function () {
            if (!sentinel.hidden) {
                _listingsSentinelObserver.observe(sentinel);
            }
        };

        const loadNextPage = async function () {
            const nextUrl = sentinel.dataset.nextUrl;
            if (!nextUrl || loadingNextPage) {
                return;
            }

            loadingNextPage = true;
            _listingsSentinelObserver.unobserve(sentinel);
            sentinel.hidden = true;
            setHiddenState(loadingMessage, false);
            setHiddenState(errorMessage, true);

            try {
                const response = await fetch(nextUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }

                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                const freshContainer = doc.getElementById('listings-content');
                if (!freshContainer) {
                    throw new Error('listings-content missing in response');
                }

                const freshGrid = freshContainer.querySelector('[data-listings-grid]');
                const freshSentinel = freshContainer.querySelector('[data-listings-sentinel]');
                const freshPagination = freshContainer.querySelector('[data-listings-pagination]');

                if (!freshGrid) {
                    throw new Error('listings grid missing in response');
                }

                // Build page section marker for the new page.
                const freshSection = freshGrid.querySelector('[data-page-section]');
                const sectionMarker = document.createElement('div');
                sectionMarker.className = 'col-span-full';
                sectionMarker.style.cssText = 'height:0;padding:0;margin:0;line-height:0';
                sectionMarker.setAttribute('aria-hidden', 'true');
                if (freshSection) {
                    sectionMarker.dataset.pageSection = freshSection.dataset.pageSection;
                    sectionMarker.dataset.pageUrl = freshSection.dataset.pageUrl;
                }
                grid.appendChild(sectionMarker);

                // Append profile cards (skip the zero-height section marker child).
                Array.from(freshGrid.children).forEach(function (child) {
                    if (!child.hasAttribute('data-page-section')) {
                        grid.appendChild(child.cloneNode(true));
                    }
                });

                // Re-initialise Alpine.js on newly appended nodes.
                if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                    window.Alpine.initTree(grid);
                }

                // Update pagination widget with the freshly loaded page's controls.
                if (freshPagination && paginationContainer) {
                    paginationContainer.innerHTML = freshPagination.innerHTML;
                }

                // Advance or close the sentinel.
                const hasMore = freshSentinel
                    && !freshSentinel.hidden
                    && (freshSentinel.dataset.nextUrl || '').trim() !== '';

                if (hasMore) {
                    sentinel.dataset.nextUrl = freshSentinel.dataset.nextUrl;
                    sentinel.hidden = false;
                    setHiddenState(endMessage, true);
                    observeSentinel();
                } else {
                    sentinel.dataset.nextUrl = '';
                    sentinel.hidden = true;
                    setHiddenState(endMessage, false);
                }

                setHiddenState(loadingMessage, true);

                // Immediately sync URL/page highlight for the newly added content.
                updateUrlFromScroll();

            } catch (_err) {
                setHiddenState(loadingMessage, true);
                setHiddenState(errorMessage, false);
                sentinel.hidden = false;
                observeSentinel();
            }

            loadingNextPage = false;
        };

        _listingsSentinelObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    loadNextPage();
                }
            });
        }, { rootMargin: '200px 0px' });

        observeSentinel();
    };

    initListingsScroll();
    window.addEventListener('listings:content-refreshed', initListingsScroll);

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
        startY: 0,
        currentX: 0,
        currentY: 0,
        dragOffset: 0,
        dragAxis: null,
        dragAxisLocked: false,
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
            const point = event.type === 'mousedown' ? event : event.touches[0];
            this.isDragging = true;
            this.startX = point.clientX;
            this.startY = point.clientY;
            this.currentX = this.startX;
            this.currentY = this.startY;
            this.dragOffset = 0;
            this.dragAxis = null;
            this.dragAxisLocked = false;
        },
        drag(event) {
            if (!this.isDragging) return;
            const isMouseEvent = event.type === 'mousemove';
            const point = isMouseEvent ? event : event.touches[0];
            this.currentX = point.clientX;
            this.currentY = point.clientY;
            const deltaX = this.currentX - this.startX;
            const deltaY = this.currentY - this.startY;

            if (!isMouseEvent) {
                if (!this.dragAxisLocked) {
                    if (Math.abs(deltaX) < 6 && Math.abs(deltaY) < 6) {
                        return;
                    }

                    this.dragAxis = Math.abs(deltaX) >= Math.abs(deltaY) ? 'x' : 'y';
                    this.dragAxisLocked = true;
                }

                if (this.dragAxis !== 'x') {
                    return;
                }
            }

            if (event.cancelable) {
                event.preventDefault();
            }
            this.dragOffset = deltaX;
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
            this.dragAxis = null;
            this.dragAxisLocked = false;
        },
    };
}
