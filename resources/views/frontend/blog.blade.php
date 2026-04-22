@extends('layouts.frontend')

@section('content')
<div class="min-h-screen bg-gray-50 px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl">
        <div class="mb-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Blog</h1>
            <p class="mt-3 text-gray-600">
                Updates about escorts, industry trends, profile tips, and platform features in Australia.
            </p>
        </div>

        <div class="space-y-4" id="blog-posts-list">
            @forelse($posts as $post)
                <a
                    href="{{ route('blog.show', $post['slug']) }}"
                    class="block rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition hover:border-pink-200"
                >
                    @if(isset($post['image']) && $post['image'])
                        <div class="mb-4 overflow-hidden rounded-xl border border-gray-200">
                            <img src="{{ $post['image'] }}" alt="{{ $post['title'] }}" class="h-48 w-full object-cover" loading="lazy" decoding="async">
                        </div>
                    @endif
                    <h2 class="mb-2 text-2xl font-bold text-gray-900">{{ $post['title'] }}</h2>
                    <p class="leading-relaxed text-gray-700">{{ $post['excerpt'] }}</p>

                    <div class="mt-3 text-sm text-gray-500">
                        Posted by {{ $post['author'] }}
                        on {{ $post['date'] }}
                    </div>
                </a>
            @empty
                <div class="rounded-2xl border border-gray-100 bg-white p-6 text-center text-gray-500 shadow-sm">
                    No blog posts have been published yet.
                </div>
            @endforelse
        </div>

        @if($hasMore)
            <div
                class="mt-4 space-y-4"
                x-data="blogInfiniteScroll({
                    initialPosts: [],
                    hasMore: @js($hasMore),
                    nextPage: @js($nextPage),
                    endpoint: @js($lazyLoadUrl),
                    postBaseUrl: @js(url('/blog'))
                })"
                x-init="init()"
            >
                <template x-for="post in posts" :key="post.slug">
                    <a
                        :href="postBaseUrl + '/' + post.slug"
                        class="block rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition hover:border-pink-200"
                    >
                        <template x-if="post.image">
                            <div class="mb-4 overflow-hidden rounded-xl border border-gray-200">
                                <img :src="post.image" :alt="post.title" class="h-48 w-full object-cover" loading="lazy" decoding="async">
                            </div>
                        </template>
                        <h2 class="mb-2 text-2xl font-bold text-gray-900" x-text="post.title"></h2>
                        <p class="leading-relaxed text-gray-700" x-text="post.excerpt"></p>

                        <div class="mt-3 text-sm text-gray-500">
                            Posted by <span x-text="post.author"></span>
                            on <span x-text="post.date"></span>
                        </div>
                    </a>
                </template>

                <div x-show="loading" x-cloak class="py-2 text-center text-sm text-gray-500">
                    Loading more blogs...
                </div>

                <div x-show="error" x-cloak class="py-2 text-center text-sm text-red-500">
                    Something went wrong while loading more blogs.
                </div>

                <div x-show="!loading && !hasMore && posts.length" x-cloak class="py-2 text-center text-sm text-gray-400">
                    No more blogs to load.
                </div>

                <div x-ref="sentinel" class="h-2 w-full"></div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('frontend/js/blog-infinite-scroll.js') }}"></script>
@endpush
