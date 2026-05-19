<?php

namespace App\Filament\Resources\LoginLogs\Pages;

use App\Filament\Resources\LoginLogs\LoginLogResource;
use App\Filament\Resources\Pages\ListRecordsWithPageJump;

class ListLoginLogs extends ListRecordsWithPageJump
{
    protected static string $resource = LoginLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
