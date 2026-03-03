@extends('layouts.frontend')

@section('title', 'FAQ')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8 mb-6">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">Frequently Asked Questions</h1>
            <p class="mt-3 text-gray-600">Find quick answers about profile management, features, and safety.</p>
        </div>

        @if(!empty($faqs))
            <div x-data="faqInfiniteScroll({ initialFaqs: @js($faqs), hasMore: @js($hasMore), nextPage: @js($nextPage), endpoint: @js($lazyLoadUrl) })" x-init="init()" class="space-y-3">
                <template x-for="(faq, index) in faqs" :key="faq.id ?? index">
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                        <button
                            type="button"
                            @click="openIndex = openIndex === index ? null : index"
                            class="w-full px-5 py-4 text-left flex items-center justify-between gap-4 hover:bg-pink-50 transition"
                        >
                            <span class="font-semibold text-gray-800" x-text="faq.question"></span>
                            <span class="text-pink-600 text-xl leading-none" x-text="openIndex === index ? '−' : '+'"></span>
                        </button>

                        <div x-show="openIndex === index" x-collapse class="px-5 pb-5">
                            <div class="pt-2 border-t border-gray-100 text-gray-600 leading-relaxed prose max-w-none" x-html="faq.answer"></div>
                        </div>
                    </div>
                </template>

                <div x-show="loading" class="text-center text-sm text-gray-500 py-2">Loading more FAQs...</div>
                <div x-show="!hasMore && faqs.length > 0" class="text-center text-sm text-gray-400 py-2">No more FAQs to load.</div>
                <div x-ref="sentinel" class="h-2"></div>
            </div>
        @else
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 text-gray-500">
                FAQs are not available yet.
            </div>
        @endif
    </div>
</div>

<script>
    function faqInfiniteScroll(config) {
        return {
            faqs: config.initialFaqs ?? [],
            hasMore: Boolean(config.hasMore),
            nextPage: Number(config.nextPage ?? 2),
            endpoint: config.endpoint,
            loading: false,
            openIndex: null,
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
                        throw new Error('Failed to load more FAQs');
                    }

                    const data = await response.json();
                    this.faqs = [...this.faqs, ...(data.faqs ?? [])];
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
