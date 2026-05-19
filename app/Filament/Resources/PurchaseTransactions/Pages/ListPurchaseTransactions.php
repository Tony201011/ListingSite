<?php

namespace App\Filament\Resources\PurchaseTransactions\Pages;

use App\Filament\Resources\PurchaseTransactions\PurchaseTransactionResource;
use App\Filament\Resources\Pages\ListRecordsWithPageJump;

class ListPurchaseTransactions extends ListRecordsWithPageJump
{
    protected static string $resource = PurchaseTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
