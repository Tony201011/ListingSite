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
        distance: config.distance || 500,
        maxDistance: config.maxDistance || 500,
        locationEnabled: config.locationEnabled || false,
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
                        name: (item.suburb || '') + ', ' + (item.state || ''),
                        label: item.postcode || '',
                        value: item.suburb || '',
                    }));
                } else {
                    this.suggestions = (data.suggestions || []).map(item => ({
                        type: 'profile',
                        name: item.name || '',
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

        selectSuggestion(item, event) {
            if (item.type === 'suburb') {
                this.term = item.value;
                this.closeSuggestions();
                const form = event.target.closest('form');
                if (form) this.$nextTick(() => form.submit());
            } else {
                window.location.href = '/profile/' + item.slug;
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
                this.selectSuggestion(this.suggestions[this.highlightedIndex], event);
                return;
            }
            event.target.closest('form').submit();
        },
    };
}

function favouriteBookmark(config) {
    return {
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
