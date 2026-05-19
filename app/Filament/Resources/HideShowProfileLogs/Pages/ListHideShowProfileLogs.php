<?php

namespace App\Filament\Resources\HideShowProfileLogs\Pages;

use App\Filament\Resources\HideShowProfileLogs\HideShowProfileLogResource;
use App\Filament\Resources\Pages\ListRecordsWithPageJump;

class ListHideShowProfileLogs extends ListRecordsWithPageJump
{
    protected static string $resource = HideShowProfileLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
