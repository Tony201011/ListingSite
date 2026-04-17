<x-filament-panels::page>
    <div
        x-data="{ modalOpen: false, modalContent: '' }"
        @keydown.escape.window="modalOpen = false"
    >
        {{-- Full-line modal --}}
        <template x-teleport="body">
            <div
                x-show="modalOpen"
                x-transition.opacity
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                style="display:none"
                @click.self="modalOpen = false"
            >
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

                <div class="relative z-10 w-full max-w-4xl rounded-xl bg-white shadow-2xl dark:bg-gray-900">
                    <div class="flex items-center justify-between border-b border-gray-200 px-5 py-3 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Full Log Entry</h3>

                        <button
                            @click="modalOpen = false"
                            class="rounded-lg p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="max-h-[75vh] overflow-auto p-5">
                        <pre class="whitespace-pre-wrap break-words font-mono text-xs leading-5 text-gray-900 dark:text-gray-100" x-text="modalContent"></pre>
                    </div>
                </div>
            </div>
        </template>

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

                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-950">
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-fixed divide-y divide-gray-200 text-xs dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="w-14 px-3 py-3 text-left font-medium text-gray-700 dark:text-gray-200">#</th>
                                    <th class="w-44 px-3 py-3 text-left font-medium text-gray-700 dark:text-gray-200">Timestamp</th>
                                    <th class="w-28 px-3 py-3 text-left font-medium text-gray-700 dark:text-gray-200">Level</th>
                                    <th class="w-28 px-3 py-3 text-left font-medium text-gray-700 dark:text-gray-200">Channel</th>
                                    <th class="px-3 py-3 text-left font-medium text-gray-700 dark:text-gray-200">Message</th>
                                    <th class="w-14 px-3 py-3 text-center font-medium text-gray-700 dark:text-gray-200">View</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-gray-950">
                                @forelse ($this->logLines as $index => $entry)
                                    <tr class="align-top {{ $index % 2 === 1 ? 'bg-gray-50 dark:bg-gray-900/40' : '' }}">
                                        <td class="px-3 py-3 font-mono text-gray-500 dark:text-gray-400">
                                            {{ $index + 1 }}
                                        </td>

                                        <td class="px-3 py-3 font-mono whitespace-nowrap text-gray-700 dark:text-gray-300">
                                            {{ $entry['timestamp'] ?: '—' }}
                                        </td>

                                        <td class="px-3 py-3">
                                            @if ($entry['level'] !== '')
                                                @php
                                                    $levelColor = match ($entry['level']) {
                                                        'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400',
                                                        'WARNING' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400',
                                                        'NOTICE' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
                                                        'INFO' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400',
                                                        'DEBUG' => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                                                        default => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                                                    };
                                                @endphp

                                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $levelColor }}">
                                                    {{ $entry['level'] }}
                                                </span>
                                            @else
                                                <span class="text-gray-400 dark:text-gray-600">—</span>
                                            @endif
                                        </td>

                                        <td class="px-3 py-3 text-gray-700 dark:text-gray-300">
                                            <div class="truncate">
                                                {{ $entry['channel'] ?: '—' }}
                                            </div>
                                        </td>

                                        <td class="px-3 py-3 font-mono text-gray-900 dark:text-gray-100">
                                            <div class="max-w-full overflow-hidden">
                                                <p class="line-clamp-2 break-words whitespace-normal leading-5">
                                                    {{ trim($entry['message']) !== '' ? $entry['message'] : '—' }}
                                                </p>
                                            </div>
                                        </td>

                                        <td class="px-3 py-3 text-center">
                                            @if ($entry['raw'] !== '')
                                                <button
                                                    type="button"
                                                    title="View full log entry"
                                                    @click="modalContent = {{ Js::from($entry['raw']) }}; modalOpen = true"
                                                    class="inline-flex items-center justify-center rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </button>
                                            @else
                                                <span class="text-gray-300 dark:text-gray-700">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $this->logStatusMessage ?? 'No log entries available.' }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
