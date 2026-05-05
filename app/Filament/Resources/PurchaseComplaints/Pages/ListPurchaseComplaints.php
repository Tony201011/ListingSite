<?php

namespace App\Filament\Resources\PurchaseComplaints\Pages;

use App\Filament\Resources\PurchaseComplaints\PurchaseComplaintResource;
use Filament\Resources\Pages\ListRecords;

class ListPurchaseComplaints extends ListRecords
{
    protected static string $resource = PurchaseComplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
