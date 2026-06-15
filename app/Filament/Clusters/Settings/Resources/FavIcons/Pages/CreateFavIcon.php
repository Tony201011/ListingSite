<?php

namespace App\Filament\Clusters\Settings\Resources\FavIcons\Pages;

use App\Filament\Concerns\ReviewerReadOnly;
use App\Filament\Clusters\Settings\Resources\FavIcons\FavIconResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFavIcon extends CreateRecord
{
    use ReviewerReadOnly;

    protected static string $resource = FavIconResource::class;
}
