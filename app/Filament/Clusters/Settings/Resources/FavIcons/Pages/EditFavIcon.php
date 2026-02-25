<?php

namespace App\Filament\Clusters\Settings\Resources\FavIcons\Pages;

use App\Filament\Clusters\Settings\Resources\FavIcons\FavIconResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditFavIcon extends EditRecord
{
    protected static string $resource = FavIconResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
