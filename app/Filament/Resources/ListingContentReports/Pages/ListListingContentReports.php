<?php

namespace App\Filament\Resources\ListingContentReports\Pages;

use App\Filament\Resources\ListingContentReports\ListingContentReportResource;
use App\Filament\Resources\Pages\ListRecordsWithPageJump;

class ListListingContentReports extends ListRecordsWithPageJump
{
    protected static string $resource = ListingContentReportResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
