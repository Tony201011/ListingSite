<?php

namespace App\Filament\Resources\PricingPackages\Pages;

use App\Filament\Resources\PricingPackages\PricingPackageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePricingPackages extends ManageRecords
{
    protected static string $resource = PricingPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Add Pricing Package'),
        ];
    }
}
