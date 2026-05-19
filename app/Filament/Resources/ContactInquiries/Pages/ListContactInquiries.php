<?php

namespace App\Filament\Resources\ContactInquiries\Pages;

use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Filament\Resources\Pages\ListRecordsWithPageJump;

class ListContactInquiries extends ListRecordsWithPageJump
{
    protected static string $resource = ContactInquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
