<x-filament-panels::page>
    <x-filament::section heading="Application Log (latest entries)">
        <div class="space-y-4">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-950">
                <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Filter logs by date</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Use the date range to narrow the log output.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <a
                            href="{{ url()->current() }}"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800"
                        >
                            Reset
                        </a>
                    </div>
                </div>

                <form method="GET" class="grid gap-4 sm:grid-cols-3">
                    <label class="block">
                        <span class="text-xs font-medium uppercase tracking-wide text-gray-600 dark:text-gray-400">From</span>
                        <input
                            type="date"
                            name="date_from"
                            value="{{ $this->dateFrom }}"
                            class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 outline-none transition duration-150 ease-in-out focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                        />
                    </label>

                    <label class="block">
                        <span class="text-xs font-medium uppercase tracking-wide text-gray-600 dark:text-gray-400">Until</span>
                        <input
                            type="date"
                            name="date_to"
                            value="{{ $this->dateTo }}"
                            class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 outline-none transition duration-150 ease-in-out focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                        />
                    </label>

                    <div class="flex items-center gap-2">
                        <button
                            type="submit"
                            class="inline-flex min-w-[8rem] items-center justify-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-700 disabled:opacity-50"
                        >
                            Apply filter
                        </button>
                    </div>
                </form>
            </div>

            <p class="text-sm text-gray-500 dark:text-gray-400">
                File: {{ $this->logFilePath }}
            </p>

            <div class="max-h-[70vh] overflow-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-950">
                <table class="w-full table-fixed divide-y divide-gray-200 text-xs dark:divide-gray-700">
                    <thead class="sticky top-0 bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="w-16 px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-200">#</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-200">Log Line</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-gray-950">
                        @forelse ($this->logLines as $index => $line)
                            <tr class="align-top">
                                <td class="w-16 px-3 py-2 font-mono text-gray-500 dark:text-gray-400">{{ $index + 1 }}</td>
                                <td class="px-3 py-2 font-mono whitespace-pre-wrap break-all text-gray-900 dark:text-gray-100">{{ $line }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-3 py-2 text-gray-500 dark:text-gray-400">{{ $this->logStatusMessage ?? 'No log entries available.' }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
