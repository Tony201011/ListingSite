<?php

namespace App\Filament\Resources\WooCommerceLogs\Pages;

use App\Filament\Resources\Pages\ListRecordsWithPageJump;
use App\Filament\Resources\WooCommerceLogs\WooCommerceLogResource;

class ListWooCommerceLogs extends ListRecordsWithPageJump
{
    protected static string $resource = WooCommerceLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
