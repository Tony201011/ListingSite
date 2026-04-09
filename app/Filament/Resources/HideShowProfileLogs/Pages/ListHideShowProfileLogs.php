<?php

namespace App\Filament\Resources\HideShowProfileLogs\Pages;

use App\Filament\Resources\HideShowProfileLogs\HideShowProfileLogResource;
use Filament\Resources\Pages\ListRecords;

class ListHideShowProfileLogs extends ListRecords
{
    protected static string $resource = HideShowProfileLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
