<?php

namespace App\Filament\Clusters\Settings\Resources\FavIcons\Pages;

use App\Filament\Clusters\Settings\Resources\FavIcons\FavIconResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFavIcon extends ViewRecord
{
    protected static string $resource = FavIconResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
