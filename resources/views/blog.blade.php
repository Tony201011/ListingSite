@extends('layouts.frontend')

@section('content')
<!-- ================= HERO BANNER (same as signup) ================= -->
<div class="relative overflow-hidden bg-gradient-to-r from-[#e04ecb] to-[#c13ab0]">
    <div class="absolute inset-0 bg-cover bg-center opacity-20" style="background-image: url('https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=1200&auto=format&fit=crop');"></div>
    <div class="relative z-10 max-w-6xl mx-auto px-5 py-16 text-center">
        <h1 class="text-5xl md:text-6xl font-extrabold text-white mb-2 drop-shadow-lg">hotescorts.com.au</h1>
        <p class="text-xl text-white/90 tracking-widest">REAL WOMEN NEAR YOU</p>
    </div>
</div>

<div class="bg-gray-50 min-h-screen py-10">
    <div class="max-w-3xl mx-auto px-5">
        <div class="bg-white border border-gray-100 rounded-2xl p-6 md:p-8 mb-6 shadow-sm">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">Hotescorts Blog</h2>
            <p class="text-gray-600">Updates about escorts, industry trends, profile tips, and platform features in Australia.</p>
        </div>

        <div class="space-y-4" x-data="blogInfiniteScroll(@js($posts))" x-init="init()">
            <template x-for="(post, index) in visiblePosts" :key="index">
                <a :href="'/blog/' + post.slug" class="block bg-white border border-gray-100 rounded-xl p-5 hover:border-pink-300 transition">
                    <h3 class="text-xl font-semibold text-pink-600 mb-2" x-text="post.title"></h3>
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
    function blogInfiniteScroll(posts) {
        return {
            posts,
            visibleCount: 5,
            step: 5,
            loading: false,
            observer: null,

            get visiblePosts() {
                return this.posts.slice(0, this.visibleCount);
            },

            get hasMore() {
                return this.visibleCount < this.posts.length;
            },

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

            loadMore() {
                if (!this.hasMore || this.loading) {
                    return;
                }

                this.loading = true;

                setTimeout(() => {
                    this.visibleCount = Math.min(this.visibleCount + this.step, this.posts.length);
                    this.loading = false;
                }, 180);
            }
        };
    }
</script>
@endsection
