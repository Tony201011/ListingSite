<?php

namespace App\Filament\Clusters\Logs\Pages;

use App\Filament\Clusters\Logs;
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

    /** @var array<int, string> */
    public array $logLines = [];

    public function mount(): void
    {
        $this->logFilePath = storage_path('logs/laravel.log');
        $this->loadLogLines();
    }

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
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
        $tailedLogContents = $this->tailLogFile($this->logFilePath);
        $this->logLines = $this->normalizeLogLines($tailedLogContents);
    }

    private function tailLogFile(string $path, int $lines = 500): string
    {
        if (! File::exists($path)) {
            return '';
        }

        if (File::size($path) === 0) {
            return '';
        }

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

        return $normalizedLogContents === '' ? [] : explode("\n", $normalizedLogContents);
    }
}
