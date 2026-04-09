<?php

namespace App\Filament\Resources\OnlineLogs\Pages;

use App\Filament\Resources\OnlineLogs\OnlineLogResource;
use Filament\Resources\Pages\ListRecords;

class ListOnlineLogs extends ListRecords
{
    protected static string $resource = OnlineLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
