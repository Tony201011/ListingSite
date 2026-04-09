<?php

namespace App\Filament\Agent\Resources\ProviderListingResource\Pages;

use App\Filament\Agent\Resources\ProviderListingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProviderListing extends EditRecord
{
    protected static string $resource = ProviderListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
