<x-filament-panels::page>
    <x-filament::section heading="Application Log (latest entries)">
        <div class="space-y-2">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                File: {{ $this->logFilePath }}
            </p>

            @php
                $logLines = preg_split('/\r\n|\r|\n/', $this->logContents) ?: [];
            @endphp

            <div class="max-h-[70vh] overflow-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 text-xs dark:divide-gray-700">
                    <thead class="sticky top-0 bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="w-16 px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-200">#</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-200">Log Line</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-gray-950">
                        @forelse ($logLines as $index => $line)
                            <tr class="align-top">
                                <td class="px-3 py-2 font-mono text-gray-500 dark:text-gray-400">{{ $index + 1 }}</td>
                                <td class="px-3 py-2 font-mono whitespace-pre-wrap break-words text-gray-900 dark:text-gray-100">{{ $line }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-3 py-2 text-gray-500 dark:text-gray-400">No log entries available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
