function blogInfiniteScroll(config = {}) {
    return {
        posts: Array.isArray(config.initialPosts) ? config.initialPosts : [],
        hasMore: Boolean(config.hasMore),
        nextPage: Number(config.nextPage || 2),
        endpoint: typeof config.endpoint === 'string' ? config.endpoint : '',
        loading: false,
        error: false,
        observer: null,
        scrollHandler: null,

        init() {
            if (!this.endpoint) {
                console.error('blogInfiniteScroll: endpoint is required.');
                return;
            }

            this.setupObserver();
        },

        setupObserver() {
            if ('IntersectionObserver' in window) {
                this.observer = new IntersectionObserver(
                    (entries) => {
                        entries.forEach((entry) => {
                            if (entry.isIntersecting) {
                                this.loadMore();
                            }
                        });
                    },
                    {
                        root: null,
                        rootMargin: '200px 0px',
                        threshold: 0,
                    }
                );

                if (this.$refs.sentinel) {
                    this.observer.observe(this.$refs.sentinel);
                }

                return;
            }

            this.scrollHandler = this.handleScroll.bind(this);
            window.addEventListener('scroll', this.scrollHandler, { passive: true });
        },

        handleScroll() {
            const nearBottom =
                window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 250;

            if (nearBottom) {
                this.loadMore();
            }
        },

        async loadMore() {
            if (!this.hasMore || this.loading || !this.endpoint) {
                return;
            }

            this.loading = true;
            this.error = false;

            try {
                const url = new URL(this.endpoint, window.location.origin);
                url.searchParams.set('page', this.nextPage);

                const response = await fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error(`Failed to load posts. Status: ${response.status}`);
                }

                const data = await response.json();
                const newPosts = Array.isArray(data.posts) ? data.posts : [];

                this.posts = [...this.posts, ...newPosts];
                this.hasMore = Boolean(data.hasMore);
                this.nextPage = Number(data.nextPage || (this.nextPage + 1));
            } catch (error) {
                this.error = true;
                console.error('blogInfiniteScroll error:', error);
            } finally {
                this.loading = false;
            }
        }
    };
}
