<?php

namespace App\Filament\Resources\OnlineLogs\Pages;

use App\Filament\Resources\OnlineLogs\OnlineLogResource;
use App\Filament\Resources\Pages\ListRecordsWithPageJump;

class ListOnlineLogs extends ListRecordsWithPageJump
{
    protected static string $resource = OnlineLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
