<?php

namespace App\Filament\Resources\PhotoVerifications\Pages;

use App\Filament\Resources\PhotoVerifications\PhotoVerificationResource;
use Filament\Resources\Pages\ListRecords;

class ListPhotoVerifications extends ListRecords
{
    protected static string $resource = PhotoVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
