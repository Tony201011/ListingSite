<?php

namespace App\Filament\Resources\ListingReports\Pages;

use App\Filament\Resources\ListingReports\ListingReportResource;
use Filament\Resources\Pages\ListRecords;

class ListListingReports extends ListRecords
{
    protected static string $resource = ListingReportResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
