<?php

namespace App\Filament\Resources\CreditLogs\Pages;

use App\Filament\Resources\CreditLogs\CreditLogResource;
use App\Filament\Resources\Pages\ListRecordsWithPageJump;

class ListCreditLogs extends ListRecordsWithPageJump
{
    protected static string $resource = CreditLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
