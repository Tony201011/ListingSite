@extends('layouts.frontend')
@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8 mb-6">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">Blog</h1>
            <p class="mt-3 text-gray-600">Updates about escorts, industry trends, profile tips, and platform features in Australia.</p>
        </div>

        <div class="space-y-4" x-data="blogInfiniteScroll({ initialPosts: @js($posts), hasMore: @js($hasMore), nextPage: @js($nextPage), endpoint: @js($lazyLoadUrl) })" x-init="init()">
            <template x-for="(post, index) in posts" :key="post.slug + '-' + index">
                <a :href="'/blog/' + post.slug" class="block bg-white rounded-2xl border border-gray-100 shadow-sm p-6 hover:border-pink-200 transition">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2" x-text="post.title"></h2>
                    <p class="text-gray-700 leading-relaxed" x-text="post.excerpt"></p>
                    <div class="mt-3 text-sm text-gray-500">
                        Posted by <span x-text="post.author"></span> on <span x-text="post.date"></span>
                    </div>
                </a>
            </template>

            <div x-show="loading" class="text-center text-sm text-gray-500 py-2">Loading more blogs...</div>
            <div x-show="!hasMore" class="text-center text-sm text-gray-400 py-2">No more blogs to load.</div>
            <div x-ref="sentinel" class="h-2"></div>
        </div>
    </div>
</div>

<script>
    function blogInfiniteScroll(config) {
        return {
            posts: config.initialPosts ?? [],
            hasMore: Boolean(config.hasMore),
            nextPage: Number(config.nextPage ?? 2),
            endpoint: config.endpoint,
            loading: false,
            observer: null,

            init() {
                this.setupObserver();
            },

            setupObserver() {
                if (!('IntersectionObserver' in window)) {
                    window.addEventListener('scroll', () => {
                        const nearBottom = window.innerHeight + window.scrollY >= document.body.offsetHeight - 250;
                        if (nearBottom) {
                            this.loadMore();
                        }
                    }, { passive: true });

                    return;
                }

                this.observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            this.loadMore();
                        }
                    });
                }, {
                    rootMargin: '200px 0px'
                });

                this.observer.observe(this.$refs.sentinel);
            },

            async loadMore() {
                if (!this.hasMore || this.loading) {
                    return;
                }

                this.loading = true;

                try {
                    const response = await fetch(`${this.endpoint}?page=${this.nextPage}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Failed to load more posts');
                    }

                    const data = await response.json();
                    this.posts = [...this.posts, ...(data.posts ?? [])];
                    this.hasMore = Boolean(data.hasMore);
                    this.nextPage = Number(data.nextPage ?? (this.nextPage + 1));
                } catch (error) {
                    console.error(error);
                } finally {
                    this.loading = false;
                }
            }
        };
    }
</script>
@endsection
