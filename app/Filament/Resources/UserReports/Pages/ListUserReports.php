<?php

namespace App\Filament\Resources\UserReports\Pages;

use App\Filament\Resources\UserReports\UserReportResource;
use App\Filament\Resources\Pages\ListRecordsWithPageJump;

class ListUserReports extends ListRecordsWithPageJump
{
    protected static string $resource = UserReportResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
