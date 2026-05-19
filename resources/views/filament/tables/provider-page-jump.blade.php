<div
    x-data="{
        pageInput: '{{ $currentPage }}',
        maxPage: {{ $lastPage }},
        goToPage(page) {
            const parsedPage = Number.parseInt(page, 10);

            if (Number.isNaN(parsedPage)) {
                return;
            }

            const nextPage = Math.min(this.maxPage, Math.max(1, parsedPage));

            this.pageInput = nextPage.toString();
            $wire.gotoPage(nextPage, '{{ $pageName }}');
        },
    }"
    class="flex flex-wrap items-center justify-end gap-2 border-t border-gray-200 px-4 py-3 dark:border-white/10"
>
    <label for="providers-page-jump" class="text-sm font-medium text-gray-700 dark:text-gray-200">Jump to page</label>
    <select
        id="providers-page-jump"
        wire:change="gotoPage($event.target.value, '{{ $pageName }}')"
        x-on:change="pageInput = $event.target.value"
        class="rounded-lg border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/15 dark:bg-white/5 dark:text-white"
    >
        @for ($page = 1; $page <= $lastPage; $page++)
            <option value="{{ $page }}" @selected($page === $currentPage)>
                {{ $page }}
            </option>
        @endfor
    </select>
    <span class="text-sm text-gray-500 dark:text-gray-400">of {{ $lastPage }}</span>
    <input
        id="providers-page-input"
        type="number"
        min="1"
        max="{{ $lastPage }}"
        inputmode="numeric"
        pattern="[0-9]*"
        x-model="pageInput"
        x-on:keydown.enter.prevent="goToPage(pageInput)"
        class="w-24 rounded-lg border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/15 dark:bg-white/5 dark:text-white"
        aria-label="Go to page number"
        placeholder="Page #"
    />
    <button
        type="button"
        x-on:click="goToPage(pageInput)"
        x-bind:disabled="pageInput === '' || Number.isNaN(Number.parseInt(pageInput, 10))"
        class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-3 py-1.5 text-sm font-medium text-white transition hover:bg-primary-500 disabled:cursor-not-allowed disabled:opacity-60"
    >
        Go
    </button>
</div>
