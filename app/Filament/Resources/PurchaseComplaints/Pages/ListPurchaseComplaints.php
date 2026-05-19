<?php

namespace App\Filament\Resources\PurchaseComplaints\Pages;

use App\Filament\Resources\PurchaseComplaints\PurchaseComplaintResource;
use App\Filament\Resources\Pages\ListRecordsWithPageJump;

class ListPurchaseComplaints extends ListRecordsWithPageJump
{
    protected static string $resource = PurchaseComplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
