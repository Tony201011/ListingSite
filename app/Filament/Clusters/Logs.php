<?php

namespace App\Filament\Clusters;

use App\Filament\Clusters\Logs\Pages\SiteLogs;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class Logs extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Logs';

    protected static ?int $navigationSort = 5;

    public static function getPages(): array
    {
        return [
            'index' => SiteLogs::route('/'),
        ];
    }
}
