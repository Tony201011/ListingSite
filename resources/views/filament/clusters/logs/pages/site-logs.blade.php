<x-filament-panels::page>
    <div
        x-data="{ modalOpen: false, modalContent: '' }"
        @keydown.escape.window="modalOpen = false"
        class="space-y-6"
    >
        {{-- Full log modal --}}
        <template x-teleport="body">
            <div
                x-show="modalOpen"
                x-transition.opacity
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                style="display:none"
                @click.self="modalOpen = false"
            >
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

                <div class="relative z-10 w-full max-w-5xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">
                    <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            Full Log Entry
                        </h3>

                        <button
                            type="button"
                            @click="modalOpen = false"
                            class="rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-gray-200"
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

        <x-filament::section heading="Application Log">
            <div class="space-y-6">
                {{-- Filter Card --}}
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-950">
                    <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                    Filter logs by date
                                </h2>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Use a date range to narrow the log output.
                                </p>
                            </div>

                            <a
                                href="{{ url()->current() }}"
                                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800"
                            >
                                Reset
                            </a>
                        </div>
                    </div>

                    <div class="px-5 py-5">
                        <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <label class="block">
                                <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">
                                    From
                                </span>
                                <input
                                    type="date"
                                    name="date_from"
                                    value="{{ $this->dateFrom }}"
                                    class="block w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 outline-none transition focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                />
                            </label>

                            <label class="block">
                                <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">
                                    Until
                                </span>
                                <input
                                    type="date"
                                    name="date_to"
                                    value="{{ $this->dateTo }}"
                                    class="block w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 outline-none transition focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                />
                            </label>

                            <div class="flex items-end">
                                <button
                                    type="submit"
                                    class="inline-flex w-full items-center justify-center rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-700 md:w-auto"
                                >
                                    Apply Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Log file path --}}
                <div class="rounded-2xl border border-gray-200 bg-white px-5 py-4 shadow-sm dark:border-gray-700 dark:bg-gray-950">
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Log File
                        </span>
                        <p class="break-all font-mono text-sm text-gray-800 dark:text-gray-200">
                            {{ $this->logFilePath }}
                        </p>
                    </div>
                </div>

                {{-- Logs Table --}}
                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-950">
                    <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            Latest Entries
                        </h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full table-fixed border-collapse text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="w-16 border border-gray-200 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:border-gray-700 dark:text-gray-300">
                                        #
                                    </th>
                                    <th class="w-48 border border-gray-200 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:border-gray-700 dark:text-gray-300">
                                        Timestamp
                                    </th>
                                    <th class="w-28 border border-gray-200 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:border-gray-700 dark:text-gray-300">
                                        Level
                                    </th>
                                    <th class="w-32 border border-gray-200 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:border-gray-700 dark:text-gray-300">
                                        Channel
                                    </th>
                                    <th class="border border-gray-200 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:border-gray-700 dark:text-gray-300">
                                        Message
                                    </th>
                                    <th class="w-24 border border-gray-200 px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-600 dark:border-gray-700 dark:text-gray-300">
                                        View
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="bg-white dark:bg-gray-950">
                                @forelse ($this->logLines as $index => $entry)
                                    <tr class="align-top hover:bg-gray-50 dark:hover:bg-gray-900/40">
                                        <td class="border border-gray-200 px-4 py-4 font-mono text-xs text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                            {{ $index + 1 }}
                                        </td>

                                        <td class="border border-gray-200 px-4 py-4 font-mono text-xs text-gray-700 dark:border-gray-700 dark:text-gray-300">
                                            <div class="whitespace-nowrap">
                                                {{ $entry['timestamp'] ?: '—' }}
                                            </div>
                                        </td>

                                        <td class="border border-gray-200 px-4 py-4 dark:border-gray-700">
                                            @if ($entry['level'] !== '')
                                                @php
                                                    $levelColor = match ($entry['level']) {
                                                        'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY' => 'bg-red-100 text-red-700 ring-red-200 dark:bg-red-900/30 dark:text-red-400 dark:ring-red-800',
                                                        'WARNING' => 'bg-yellow-100 text-yellow-700 ring-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-400 dark:ring-yellow-800',
                                                        'NOTICE' => 'bg-blue-100 text-blue-700 ring-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:ring-blue-800',
                                                        'INFO' => 'bg-green-100 text-green-700 ring-green-200 dark:bg-green-900/30 dark:text-green-400 dark:ring-green-800',
                                                        'DEBUG' => 'bg-gray-100 text-gray-700 ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700',
                                                        default => 'bg-gray-100 text-gray-700 ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700',
                                                    };
                                                @endphp

                                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset {{ $levelColor }}">
                                                    {{ $entry['level'] }}
                                                </span>
                                            @else
                                                <span class="text-sm text-gray-400 dark:text-gray-600">—</span>
                                            @endif
                                        </td>

                                        <td class="border border-gray-200 px-4 py-4 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-300">
                                            <div class="truncate">
                                                {{ $entry['channel'] ?: '—' }}
                                            </div>
                                        </td>

                                        <td class="border border-gray-200 px-4 py-4 dark:border-gray-700">
                                            <div class="max-w-full overflow-hidden">
                                                <p
                                                    class="font-mono text-xs leading-5 text-gray-900 dark:text-gray-100"
                                                    style="
                                                        display: -webkit-box;
                                                        -webkit-line-clamp: 3;
                                                        -webkit-box-orient: vertical;
                                                        overflow: hidden;
                                                        word-break: break-word;
                                                    "
                                                >
                                                    {{ trim($entry['message']) !== '' ? preg_replace('/\s+/', ' ', $entry['message']) : '—' }}
                                                </p>
                                            </div>
                                        </td>

                                        <td class="border border-gray-200 px-4 py-4 text-center dark:border-gray-700">
                                            @if ($entry['raw'] !== '')
                                                <button
                                                    type="button"
                                                    title="View full log entry"
                                                    @click="modalContent = {{ Js::from($entry['raw']) }}; modalOpen = true"
                                                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-primary-200 bg-primary-50 text-primary-600 transition hover:bg-primary-100 hover:text-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-primary-800 dark:bg-primary-900/20 dark:text-primary-400 dark:hover:bg-primary-900/30"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </button>
                                            @else
                                                <span class="text-sm text-gray-300 dark:text-gray-700">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="border border-gray-200 px-4 py-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
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
