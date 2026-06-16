<?php

namespace App\Filament\Resources\RestoreAccountRequests\Pages;

use App\Filament\Resources\Pages\ListRecordsWithPageJump;
use App\Filament\Resources\RestoreAccountRequests\RestoreAccountRequestResource;

class ListRestoreAccountRequests extends ListRecordsWithPageJump
{
    protected static string $resource = RestoreAccountRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
