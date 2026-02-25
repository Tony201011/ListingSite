<?php

namespace App\Filament\Clusters\Settings\Resources\FavIcons\Pages;

use App\Filament\Clusters\Settings\Resources\FavIcons\FavIconResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFavIcons extends ListRecords
{
    protected static string $resource = FavIconResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
