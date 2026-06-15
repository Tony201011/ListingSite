@if ($paginator->hasPages() || $paginator->total() > 0)
    @php
        $from = $paginator->firstItem() ?? 0;
        $to = $paginator->lastItem() ?? 0;
        $total = $paginator->total();
        $currentPerPage = $paginator->perPage();
        $allowedPerPage = collect([12, 24, 48]);
        if (! $allowedPerPage->contains($currentPerPage)) {
            $allowedPerPage = $allowedPerPage->push($currentPerPage)->sort()->values();
        }

        // Build the page-1 URL with per_page stripped, for the per_page selector navigation
        $pageOneUrl = $paginator->url(1);
        $parsedPageOne = parse_url($pageOneUrl);
        parse_str($parsedPageOne['query'] ?? '', $pageOneQuery);
        unset($pageOneQuery['per_page']);
        $perPageBaseUrl = ($parsedPageOne['path'] ?? '/') . ($pageOneQuery ? '?' . http_build_query($pageOneQuery) : '');
    @endphp
    <nav
        role="navigation"
        aria-label="Homepage pagination"
        x-data="{
            pageInput: '{{ $paginator->currentPage() }}',
            maxPage: {{ $paginator->lastPage() }},
            pageTwoUrl: '{{ addslashes($paginator->url(2)) }}',
            pageOneUrl: '{{ addslashes($paginator->url(1)) }}',
            perPageBaseUrl: '{{ addslashes($perPageBaseUrl) }}',
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
            changePerPage(value) {
                const url = new URL(this.perPageBaseUrl, window.location.origin);
                url.searchParams.set('per_page', value);
                window.location.href = url.href;
            },
        }"
        class="mt-6 flex flex-col gap-3"
    >
        {{-- Main pagination row --}}
        <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 shadow-sm">

            {{-- Left: Show rows selector --}}
            <div class="flex items-center gap-2">
                <label for="home-per-page" class="text-sm font-medium text-gray-600 whitespace-nowrap">Show rows</label>
                <select
                    id="home-per-page"
                    x-on:change="changePerPage($event.target.value)"
                    class="rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-sm text-gray-900 shadow-sm focus:border-pink-500 focus:outline-none focus:ring-1 focus:ring-pink-500"
                >
                    @foreach ($allowedPerPage as $option)
                        <option value="{{ $option }}" @selected($option === $currentPerPage)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Centre: Range text --}}
            <span class="text-sm font-medium text-gray-600">
                @if ($total > 0)
                    {{ number_format($from) }}&nbsp;–&nbsp;{{ number_format($to) }} of {{ number_format($total) }}
                @else
                    0 results
                @endif
            </span>

            {{-- Right: Prev / Go-to-page / Next --}}
            <div class="flex items-center gap-2">
                {{-- Previous --}}
                @if ($paginator->onFirstPage())
                    <span class="inline-flex h-9 w-9 cursor-not-allowed items-center justify-center rounded-lg border border-gray-200 bg-gray-100 text-gray-400" aria-disabled="true" aria-label="Previous page">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-pink-200 bg-white text-pink-600 transition hover:border-pink-300 hover:bg-pink-50" aria-label="Previous page">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                @endif

                {{-- Go to page --}}
                <select
                    x-on:change="goToPage($event.target.value); pageInput = $event.target.value"
                    class="rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-sm text-gray-900 shadow-sm focus:border-pink-500 focus:outline-none focus:ring-1 focus:ring-pink-500"
                    aria-label="Go to page"
                >
                    @for ($p = 1; $p <= $paginator->lastPage(); $p++)
                        <option value="{{ $p }}" @selected($p === $paginator->currentPage())>{{ $p }}</option>
                    @endfor
                </select>
                <span class="text-sm text-gray-500 whitespace-nowrap">of {{ $paginator->lastPage() }}</span>

                {{-- Next --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-pink-200 bg-white text-pink-600 transition hover:border-pink-300 hover:bg-pink-50" aria-label="Next page">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                @else
                    <span class="inline-flex h-9 w-9 cursor-not-allowed items-center justify-center rounded-lg border border-gray-200 bg-gray-100 text-gray-400" aria-disabled="true" aria-label="Next page">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
