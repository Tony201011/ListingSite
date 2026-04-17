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

    /** @var array<int, string> */
    public array $logLines = [];

    public ?string $logStatusMessage = null;

    public function mount(): void
    {
        $this->logFilePath = storage_path('logs/laravel.log');
        $this->dateFrom = request()->query('date_from');
        $this->dateTo = request()->query('date_to');

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
            Action::make('clearFile')
                ->label('Clear File')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->action(fn () => $this->clearLogFile()),
        ];
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
        $this->logStatusMessage = null;
        $this->logLines = $this->normalizeLogLines($tailedLogContents);

        if ($this->dateFrom || $this->dateTo) {
            $this->logLines = $this->filterLogLinesByDate($this->logLines);
        }
    }

    /**
     * @param  array<int, string>  $logLines
     * @return array<int, string>
     */
    private function filterLogLinesByDate(array $logLines): array
    {
        $from = $this->dateFrom ? \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $this->dateFrom.' 00:00:00') : null;
        $to = $this->dateTo ? \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $this->dateTo.' 23:59:59') : null;

        return array_values(array_filter($logLines, function (string $line) use ($from, $to): bool {
            $timestamp = $this->parseLogLineTimestamp($line);

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

    private function parseLogLineTimestamp(string $line): ?\DateTimeImmutable
    {
        if (! preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $matches)) {
            return null;
        }

        return \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $matches[1]) ?: null;
    }

    private function tailLogFile(string $path, int $lines = 500): string
    {
        $handle = fopen($path, 'rb');

        if (! $handle) {
            return 'Unable to read the application log file. Please verify file permissions.';
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
     * @return array<int, string>
     */
    private function normalizeLogLines(string $contents): array
    {
        $normalizedLogContents = str_replace(["\r\n", "\r"], "\n", $contents);

        return $normalizedLogContents === '' ? [] : array_reverse(explode("\n", $normalizedLogContents));
    }
}
