<?php

namespace App\Filament\Clusters;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class Settings extends Cluster
{
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Setting';

    protected static ?int $navigationSort = 4;

    public static function getPages(): array
    {
        return [
            \App\Filament\Clusters\Settings\Resources\SiteSettingResource::class,
        ];
    }
}
