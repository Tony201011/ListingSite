@if ($paginator->hasPages())
    <nav
        role="navigation"
        aria-label="Homepage pagination"
        class="flex flex-col items-center gap-4"
        x-data="{
            pageInput: '{{ $paginator->currentPage() }}',
            maxPage: {{ $paginator->lastPage() }},
            pageTwoUrl: '{{ addslashes($paginator->url(2)) }}',
            pageOneUrl: '{{ addslashes($paginator->url(1)) }}',
            pageUrl(page) {
                const n = parseInt(page, 10);
                if (isNaN(n) || n < 1) return this.pageOneUrl;
                if (n === 1) return this.pageOneUrl;
                return this.pageTwoUrl.replace('/page/2', '/page/' + n);
            },
            goToPage(page) {
                const n = parseInt(page, 10);
                if (isNaN(n) || n < 1 || n > this.maxPage) return;
                window.location.href = this.pageUrl(n);
            },
        }"
    >
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
                            <a href="{{ $url }}" data-page-link="{{ $page }}" aria-current="page" class="inline-flex h-11 min-w-11 items-center justify-center rounded-xl border border-pink-600 bg-pink-600 px-4 text-sm font-semibold text-white shadow-sm" aria-label="Go to page {{ $page }}">
                                {{ $page }}
                            </a>
                        @else
                            <a href="{{ $url }}" data-page-link="{{ $page }}" class="inline-flex h-11 min-w-11 items-center justify-center rounded-xl border border-pink-200 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:border-pink-300 hover:bg-pink-50 hover:text-pink-600" aria-label="Go to page {{ $page }}">
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

        {{-- Jump to page --}}
        <div class="flex flex-wrap items-center justify-center gap-2 border-t border-gray-200 pt-3">
            <label for="page-jump-select" class="text-sm font-medium text-gray-600">Jump to page</label>
            <select
                id="page-jump-select"
                x-on:change="goToPage($event.target.value)"
                class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-900 shadow-sm focus:border-pink-500 focus:outline-none focus:ring-1 focus:ring-pink-500"
            >
                @for ($p = 1; $p <= $paginator->lastPage(); $p++)
                    <option value="{{ $p }}" @selected($p === $paginator->currentPage())>{{ $p }}</option>
                @endfor
            </select>
            <span class="text-sm text-gray-500">of {{ $paginator->lastPage() }}</span>
            <input
                type="number"
                min="1"
                max="{{ $paginator->lastPage() }}"
                inputmode="numeric"
                pattern="[0-9]*"
                x-model="pageInput"
                x-on:keydown.enter.prevent="goToPage(pageInput)"
                class="w-20 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-900 shadow-sm focus:border-pink-500 focus:outline-none focus:ring-1 focus:ring-pink-500"
                aria-label="Go to page number"
                placeholder="Page #"
            />
            <button
                type="button"
                x-on:click="goToPage(pageInput)"
                x-bind:disabled="pageInput === '' || isNaN(parseInt(pageInput, 10))"
                class="inline-flex items-center justify-center rounded-lg bg-pink-600 px-3 py-1.5 text-sm font-medium text-white transition hover:bg-pink-500 disabled:cursor-not-allowed disabled:opacity-60"
            >
                Go
            </button>
        </div>
    </nav>
@endif
