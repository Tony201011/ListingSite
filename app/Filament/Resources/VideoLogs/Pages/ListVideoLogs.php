<?php

namespace App\Filament\Resources\VideoLogs\Pages;

use App\Filament\Resources\VideoLogs\VideoLogResource;
use App\Filament\Resources\Pages\ListRecordsWithPageJump;

class ListVideoLogs extends ListRecordsWithPageJump
{
    protected static string $resource = VideoLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
