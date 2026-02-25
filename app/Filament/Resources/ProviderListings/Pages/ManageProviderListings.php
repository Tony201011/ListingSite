<?php

namespace App\Filament\Resources\ProviderListings\Pages;

use App\Filament\Resources\ProviderListings\ProviderListingResource;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ManageRecords;

class ManageProviderListings extends ManageRecords
{
    protected static string $resource = ProviderListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Listing')
                ->mutateDataUsing(function (array $data): array {
                    if (Filament::getCurrentPanel()?->getId() === 'provider') {
                        $data['user_id'] = Filament::auth()->id();
                    }

                    return $data;
                }),
        ];
    }
}
