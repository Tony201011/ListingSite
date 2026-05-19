<?php

namespace App\Filament\Resources\AvailabilityLogs\Pages;

use App\Filament\Resources\AvailabilityLogs\AvailabilityLogResource;
use App\Filament\Resources\Pages\ListRecordsWithPageJump;

class ListAvailabilityLogs extends ListRecordsWithPageJump
{
    protected static string $resource = AvailabilityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
