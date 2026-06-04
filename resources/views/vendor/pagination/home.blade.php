@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Homepage pagination" class="flex flex-col items-center gap-4">
        <div class="flex w-full items-center justify-between gap-3 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex min-w-24 cursor-not-allowed items-center justify-center rounded-xl border border-gray-200 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-400">
                    Previous
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex min-w-24 items-center justify-center rounded-xl border border-pink-200 bg-white px-4 py-2 text-sm font-semibold text-pink-600 transition hover:border-pink-300 hover:bg-pink-50">
                    Previous
                </a>
            @endif

            <span class="text-sm font-medium text-gray-600">
                Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex min-w-24 items-center justify-center rounded-xl border border-pink-200 bg-white px-4 py-2 text-sm font-semibold text-pink-600 transition hover:border-pink-300 hover:bg-pink-50">
                    Next
                </a>
            @else
                <span class="inline-flex min-w-24 cursor-not-allowed items-center justify-center rounded-xl border border-gray-200 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-400">
                    Next
                </span>
            @endif
        </div>

        <div class="hidden flex-wrap items-center justify-center gap-2 sm:flex">
            @if ($paginator->onFirstPage())
                <span class="inline-flex h-11 min-w-11 cursor-not-allowed items-center justify-center rounded-xl border border-gray-200 bg-gray-100 px-4 text-sm font-medium text-gray-400">
                    Previous
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex h-11 min-w-11 items-center justify-center rounded-xl border border-pink-200 bg-white px-4 text-sm font-semibold text-pink-600 transition hover:border-pink-300 hover:bg-pink-50">
                    Previous
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="inline-flex h-11 min-w-11 items-center justify-center rounded-xl border border-transparent px-2 text-sm font-medium text-gray-400">
                        {{ $element }}
                    </span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="inline-flex h-11 min-w-11 items-center justify-center rounded-xl border border-pink-600 bg-pink-600 px-4 text-sm font-semibold text-white shadow-sm">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="inline-flex h-11 min-w-11 items-center justify-center rounded-xl border border-pink-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:border-pink-300 hover:bg-pink-50 hover:text-pink-600" aria-label="Go to page {{ $page }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex h-11 min-w-11 items-center justify-center rounded-xl border border-pink-200 bg-white px-4 text-sm font-semibold text-pink-600 transition hover:border-pink-300 hover:bg-pink-50">
                    Next
                </a>
            @else
                <span class="inline-flex h-11 min-w-11 cursor-not-allowed items-center justify-center rounded-xl border border-gray-200 bg-gray-100 px-4 text-sm font-medium text-gray-400">
                    Next
                </span>
            @endif
        </div>
    </nav>
@endif
