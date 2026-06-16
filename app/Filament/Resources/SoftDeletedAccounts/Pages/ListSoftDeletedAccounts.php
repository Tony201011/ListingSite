<?php

namespace App\Filament\Resources\SoftDeletedAccounts\Pages;

use App\Filament\Resources\Pages\ListRecordsWithPageJump;
use App\Filament\Resources\SoftDeletedAccounts\SoftDeletedAccountResource;

class ListSoftDeletedAccounts extends ListRecordsWithPageJump
{
    protected static string $resource = SoftDeletedAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
