<?php

namespace App\Filament\Resources\UserReports\Pages;

use App\Filament\Resources\Pages\ListRecordsWithPageJump;
use App\Filament\Resources\UserReports\UserReportResource;

class ListUserReports extends ListRecordsWithPageJump
{
    protected static string $resource = UserReportResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
