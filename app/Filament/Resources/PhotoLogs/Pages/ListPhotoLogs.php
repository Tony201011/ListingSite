<?php

namespace App\Filament\Resources\PhotoLogs\Pages;

use App\Filament\Resources\Pages\ListRecordsWithPageJump;
use App\Filament\Resources\PhotoLogs\PhotoLogResource;

class ListPhotoLogs extends ListRecordsWithPageJump
{
    protected static string $resource = PhotoLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
