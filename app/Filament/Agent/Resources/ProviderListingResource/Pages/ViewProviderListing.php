<?php

namespace App\Filament\Agent\Resources\ProviderListingResource\Pages;

use App\Filament\Agent\Resources\ProviderListingResource;
use Filament\Resources\Pages\ViewRecord;

class ViewProviderListing extends ViewRecord
{
    protected static string $resource = ProviderListingResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
