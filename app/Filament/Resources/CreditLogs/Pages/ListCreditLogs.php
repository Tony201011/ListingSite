<?php

namespace App\Filament\Resources\CreditLogs\Pages;

use App\Filament\Resources\CreditLogs\CreditLogResource;
use Filament\Resources\Pages\ListRecords;

class ListCreditLogs extends ListRecords
{
    protected static string $resource = CreditLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
