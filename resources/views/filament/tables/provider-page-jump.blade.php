<div class="flex items-center justify-end gap-2 border-t border-gray-200 px-4 py-3 dark:border-white/10">
    <label for="providers-page-jump" class="text-sm font-medium text-gray-700 dark:text-gray-200">Go to page</label>
    <select
        id="providers-page-jump"
        wire:change="gotoPage($event.target.value, '{{ $pageName }}')"
        class="rounded-lg border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/15 dark:bg-white/5 dark:text-white"
    >
        @for ($page = 1; $page <= $lastPage; $page++)
            <option value="{{ $page }}" @selected($page === $currentPage)>
                {{ $page }}
            </option>
        @endfor
    </select>
    <span class="text-sm text-gray-500 dark:text-gray-400">of {{ $lastPage }}</span>
</div>
