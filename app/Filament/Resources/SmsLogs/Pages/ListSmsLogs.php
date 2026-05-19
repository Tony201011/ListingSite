<?php

namespace App\Filament\Resources\SmsLogs\Pages;

use App\Filament\Resources\SmsLogs\SmsLogResource;
use App\Filament\Resources\Pages\ListRecordsWithPageJump;

class ListSmsLogs extends ListRecordsWithPageJump
{
    protected static string $resource = SmsLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
