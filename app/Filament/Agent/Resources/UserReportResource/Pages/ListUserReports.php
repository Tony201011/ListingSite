<?php

namespace App\Filament\Agent\Resources\UserReportResource\Pages;

use App\Filament\Agent\Resources\UserReportResource;
use Filament\Resources\Pages\ListRecords;

class ListUserReports extends ListRecords
{
    protected static string $resource = UserReportResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
