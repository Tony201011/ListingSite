@extends('layouts.frontend')

@section('title', $page?->title ?: 'FAQ')

@section('content')
<div class="min-h-screen bg-gray-50 px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl">
        <div class="mb-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                {{ $page?->title ?: 'Frequently Asked Questions' }}
            </h1>
            <p class="mt-3 text-gray-600">
                {{ $page?->subtitle ?: 'Find quick answers about profile management, features, and safety.' }}
            </p>
        </div>

        @if (!empty($faqs))
            <div
                class="space-y-3"
                x-data="faqInfiniteScroll({
                    initialFaqs: @js($faqs),
                    hasMore: @js($hasMore),
                    nextPage: @js($nextPage),
                    endpoint: @js($lazyLoadUrl)
                })"
                x-init="init()"
            >
                <template x-for="(faq, index) in faqs" :key="faq.id ?? `faq-${index}`">
                    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left transition hover:bg-pink-50"
                            @click="toggle(index)"
                            :aria-expanded="openIndex === index"
                        >
                            <span class="font-semibold text-gray-800" x-text="faq.question"></span>
                            <span
                                class="text-xl leading-none text-pink-600"
                                x-text="openIndex === index ? '−' : '+'"
                            ></span>
                        </button>

                        <div x-show="openIndex === index" x-collapse class="px-5 pb-5">
                            <div
                                class="prose max-w-none border-t border-gray-100 pt-2 leading-relaxed text-gray-600"
                                x-html="faq.answer"
                            ></div>
                        </div>
                    </div>
                </template>

                <div x-show="loading" x-cloak class="py-2 text-center text-sm text-gray-500">
                    Loading more FAQs...
                </div>

                <div x-show="error" x-cloak class="py-2 text-center text-sm text-red-500">
                    Something went wrong while loading more FAQs.
                </div>

                <div x-show="!loading && !hasMore && faqs.length" x-cloak class="py-2 text-center text-sm text-gray-400">
                    No more FAQs to load.
                </div>

                <div x-ref="sentinel" class="h-2 w-full"></div>
            </div>
        @else
            <div class="rounded-xl border border-gray-100 bg-white p-6 text-gray-500 shadow-sm">
                FAQs are not available yet.
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('frontend/js/faq-infinite-scroll.js') }}"></script>
@endpush
