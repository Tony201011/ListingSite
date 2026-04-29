<?php

namespace App\Filament\Clusters\Logs\Pages;

use App\Filament\Clusters\Logs;
use App\Models\SiteSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\File;

class SiteLogs extends Page
{
    private const CHUNK_SIZE = 4096;

    private const MAX_BUFFER_BYTES = 1024 * 1024;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Site Logs';

    protected static ?string $title = 'Site Logs';

    protected static ?int $navigationSort = 0;

    protected static ?string $cluster = Logs::class;

    protected static ?string $slug = 'site-logs';

    protected string $view = 'filament.clusters.logs.pages.site-logs';

    public string $logFilePath = '';

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    /** @var array<int, array{timestamp: string, level: string, channel: string, message: string, raw: string}> */
    public array $logLines = [];

    public ?string $logStatusMessage = null;

    public function mount(): void
    {
        $this->logFilePath = $this->resolveLogFilePath();
        $this->dateFrom = $this->sanitizeDateInput(request()->query('date_from'));
        $this->dateTo = $this->sanitizeDateInput(request()->query('date_to'));

        $this->loadLogLines();
    }

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin'
            && SiteSetting::isLoggingEnabled();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(fn () => $this->refreshLogLines()),
            Action::make('clearFile')
                ->label('Clear File')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->action(fn () => $this->clearLogFile()),
        ];
    }

    public function refreshLogLines(): void
    {
        $this->loadLogLines();

        Notification::make()
            ->title('Log file refreshed.')
            ->success()
            ->send();
    }

    public function clearLogFile(): void
    {
        File::ensureDirectoryExists(dirname($this->logFilePath));
        File::put($this->logFilePath, '');

        $this->loadLogLines();

        Notification::make()
            ->title('Log file cleared successfully.')
            ->success()
            ->send();
    }

    private function resolveLogFilePath(): string
    {
        $channel = config('logging.default', 'stack');
        $channelConfig = config("logging.channels.{$channel}", []);

        // For the stack driver, resolve the first stacked channel.
        if (($channelConfig['driver'] ?? null) === 'stack') {
            $channels = $channelConfig['channels'] ?? ['single'];
            $first = is_array($channels) ? ($channels[0] ?? 'single') : 'single';
            $channelConfig = config("logging.channels.{$first}", []);
        }

        $basePath = $channelConfig['path'] ?? storage_path('logs/laravel.log');

        // For the daily driver, Monolog appends the current date to the filename.
        if (($channelConfig['driver'] ?? null) === 'daily') {
            $dir = dirname($basePath);
            $stem = pathinfo($basePath, PATHINFO_FILENAME);
            $ext = pathinfo($basePath, PATHINFO_EXTENSION);

            return $dir.DIRECTORY_SEPARATOR.$stem.'-'.date('Y-m-d').($ext !== '' ? ".{$ext}" : '');
        }

        return $basePath;
    }

    private function sanitizeDateInput(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        // Accept only YYYY-MM-DD format to prevent invalid or malicious input.
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        return \DateTimeImmutable::createFromFormat('Y-m-d', $value) instanceof \DateTimeImmutable ? $value : null;
    }

    private function loadLogLines(): void
    {
        if (! File::exists($this->logFilePath)) {
            $this->logStatusMessage = 'No application log file was found.';
            $this->logLines = [];

            return;
        }

        if (File::size($this->logFilePath) === 0) {
            $this->logStatusMessage = 'The application log file is currently empty.';
            $this->logLines = [];

            return;
        }

        $tailedLogContents = $this->tailLogFile($this->logFilePath);

        if ($tailedLogContents === null) {
            $this->logStatusMessage = 'Unable to read the application log file. Please verify file permissions.';
            $this->logLines = [];

            return;
        }

        $this->logStatusMessage = null;
        $this->logLines = $this->normalizeLogLines($tailedLogContents);

        if ($this->dateFrom || $this->dateTo) {
            $this->logLines = $this->filterLogLinesByDate($this->logLines);
        }
    }

    /**
     * @param  array<int, array{timestamp: string, level: string, channel: string, message: string, raw: string}>  $logLines
     * @return array<int, array{timestamp: string, level: string, channel: string, message: string, raw: string}>
     */
    private function filterLogLinesByDate(array $logLines): array
    {
        $from = $this->dateFrom
            ? \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $this->dateFrom.' 00:00:00') ?: null
            : null;
        $to = $this->dateTo
            ? \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $this->dateTo.' 23:59:59') ?: null
            : null;

        return array_values(array_filter($logLines, function (array $entry) use ($from, $to): bool {
            $timestamp = $entry['timestamp'] !== ''
                ? \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $entry['timestamp']) ?: null
                : null;

            if ($timestamp === null) {
                return $from === null && $to === null;
            }

            if ($from !== null && $timestamp < $from) {
                return false;
            }

            if ($to !== null && $timestamp > $to) {
                return false;
            }

            return true;
        }));
    }

    /**
     * @return array{timestamp: string, level: string, channel: string, message: string, raw: string}
     */
    private function parseLogLine(string $line): array
    {
        $pattern = '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] ([\w-]+)\.([\w-]+): (.+)$/s';

        if (preg_match($pattern, $line, $m)) {
            return [
                'timestamp' => $m[1],
                'channel' => $m[2],
                'level' => strtoupper($m[3]),
                'message' => $m[4],
                'raw' => $line,
            ];
        }

        return [
            'timestamp' => '',
            'channel' => '',
            'level' => '',
            'message' => $line,
            'raw' => $line,
        ];
    }

    private function tailLogFile(string $path, int $lines = 500): ?string
    {
        $handle = fopen($path, 'rb');

        if (! $handle) {
            return null;
        }

        $buffer = '';
        $lineBreaks = 0;
        $size = File::size($path);
        $position = $size;

        try {
            while ($position > 0 && $lineBreaks < $lines) {
                $readSize = min(self::CHUNK_SIZE, $position);

                if (strlen($buffer) + $readSize > self::MAX_BUFFER_BYTES) {
                    $readSize = self::MAX_BUFFER_BYTES - strlen($buffer);
                }

                if ($readSize <= 0) {
                    break;
                }

                $position -= $readSize;
                fseek($handle, $position);
                $chunk = fread($handle, $readSize);

                if ($chunk === false) {
                    break;
                }

                $buffer = $chunk.$buffer;
                $lineBreaks += substr_count($chunk, "\n");
            }
        } finally {
            fclose($handle);
        }

        $normalizedBuffer = str_replace(["\r\n", "\r"], "\n", rtrim($buffer, "\r\n"));
        $logLines = explode("\n", $normalizedBuffer);

        if ($position > 0) {
            array_shift($logLines);
        }

        return collect($logLines)
            ->slice(-$lines)
            ->implode(PHP_EOL);
    }

    /**
     * @return array<int, array{timestamp: string, level: string, channel: string, message: string, raw: string}>
     */
    private function normalizeLogLines(string $contents): array
    {
        $normalizedLogContents = str_replace(["\r\n", "\r"], "\n", $contents);

        if ($normalizedLogContents === '') {
            return [];
        }

        $rawLines = explode("\n", $normalizedLogContents);

        // Group continuation lines (stack traces, etc.) with their parent entry.
        // A new entry always starts with "[YYYY-MM-DD HH:MM:SS]".
        $entryPattern = '/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]/';
        $grouped = [];
        $current = null;

        foreach ($rawLines as $line) {
            if (preg_match($entryPattern, $line)) {
                if ($current !== null) {
                    $grouped[] = $current;
                }
                $current = $line;
            } elseif ($current !== null) {
                $current .= "\n".$line;
            } else {
                // Lines before the first structured entry (e.g. orphaned stack traces)
                $grouped[] = $line;
            }
        }

        if ($current !== null) {
            $grouped[] = $current;
        }

        return array_values(array_map(fn (string $entry) => $this->parseLogLine($entry), array_reverse($grouped)));
    }
}
