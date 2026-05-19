<?php

namespace App\Filament\Resources\EmailLogs\Pages;

use App\Filament\Resources\EmailLogs\EmailLogResource;
use App\Filament\Resources\Pages\ListRecordsWithPageJump;

class ListEmailLogs extends ListRecordsWithPageJump
{
    protected static string $resource = EmailLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
