<x-filament-panels::page>
    <x-filament::section heading="Application Log (latest entries)">
        <div class="space-y-2">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                File: {{ $this->logFilePath }}
            </p>

            <pre class="max-h-[70vh] overflow-auto rounded-lg border border-gray-200 bg-gray-50 p-4 text-xs leading-5 text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">{{ $this->logContents }}</pre>
        </div>
    </x-filament::section>
</x-filament-panels::page>
