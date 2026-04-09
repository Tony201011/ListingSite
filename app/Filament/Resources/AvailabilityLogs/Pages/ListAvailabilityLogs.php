<?php

namespace App\Filament\Resources\AvailabilityLogs\Pages;

use App\Filament\Resources\AvailabilityLogs\AvailabilityLogResource;
use Filament\Resources\Pages\ListRecords;

class ListAvailabilityLogs extends ListRecords
{
    protected static string $resource = AvailabilityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
