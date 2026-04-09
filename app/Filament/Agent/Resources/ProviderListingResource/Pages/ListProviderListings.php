<?php

namespace App\Filament\Agent\Resources\ProviderListingResource\Pages;

use App\Filament\Agent\Resources\ProviderListingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProviderListings extends ListRecords
{
    protected static string $resource = ProviderListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Create Listing'),
        ];
    }
}
