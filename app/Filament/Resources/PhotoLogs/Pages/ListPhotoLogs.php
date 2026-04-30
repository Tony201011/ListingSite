<?php

namespace App\Filament\Resources\PhotoLogs\Pages;

use App\Filament\Resources\PhotoLogs\PhotoLogResource;
use Filament\Resources\Pages\ListRecords;

class ListPhotoLogs extends ListRecords
{
    protected static string $resource = PhotoLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
