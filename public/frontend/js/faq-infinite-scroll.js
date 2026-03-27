function faqInfiniteScroll(config = {}) {
    return {
        faqs: Array.isArray(config.initialFaqs) ? config.initialFaqs : [],
        hasMore: Boolean(config.hasMore),
        nextPage: Number(config.nextPage || 2),
        endpoint: typeof config.endpoint === 'string' ? config.endpoint : '',
        loading: false,
        error: false,
        openIndex: null,
        observer: null,
        scrollHandler: null,

        init() {
            if (!this.endpoint) {
                console.error('faqInfiniteScroll: endpoint is required.');
                return;
            }

            this.setupObserver();
        },

        toggle(index) {
            this.openIndex = this.openIndex === index ? null : index;
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
                    throw new Error(`Failed to load FAQs. Status: ${response.status}`);
                }

                const data = await response.json();
                const newFaqs = Array.isArray(data.faqs) ? data.faqs : [];

                this.faqs = [...this.faqs, ...newFaqs];
                this.hasMore = Boolean(data.hasMore);
                this.nextPage = Number(data.nextPage || (this.nextPage + 1));
            } catch (error) {
                this.error = true;
                console.error('faqInfiniteScroll error:', error);
            } finally {
                this.loading = false;
            }
        }
    };
}
