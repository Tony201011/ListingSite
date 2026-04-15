<?php

namespace App\Filament\Clusters\Logs\Pages;

use App\Filament\Clusters\Logs;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\File;

class SiteLogs extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Site Logs';

    protected static ?string $title = 'Site Logs';

    protected static ?int $navigationSort = 0;

    protected static ?string $cluster = Logs::class;

    protected static ?string $slug = 'site-logs';

    protected string $view = 'filament.clusters.logs.pages.site-logs';

    public string $logFilePath = '';

    public string $logContents = '';

    public function mount(): void
    {
        $this->logFilePath = storage_path('logs/laravel.log');
        $this->logContents = $this->tailLogFile($this->logFilePath);
    }

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    private function tailLogFile(string $path, int $lines = 500): string
    {
        if (! File::exists($path)) {
            return 'No application log file was found.';
        }

        $contents = File::get($path);

        if ($contents === '') {
            return 'The application log file is currently empty.';
        }

        return collect(preg_split("/\r\n|\n|\r/", $contents))
            ->filter(fn (?string $line): bool => $line !== null)
            ->take(-$lines)
            ->implode(PHP_EOL);
    }
}
