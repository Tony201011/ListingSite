<?php

namespace App\Filament\Clusters;

use App\Filament\Clusters\Logs\Pages\SiteLogs;
use App\Models\SiteSetting;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Facades\Filament;
use Filament\Support\Icons\Heroicon;

class Logs extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Logs';

    protected static ?int $navigationSort = 5;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin'
            && SiteSetting::isLoggingEnabled();
    }

    public static function getPages(): array
    {
        return [
            'index' => SiteLogs::route('/'),
        ];
    }
}
