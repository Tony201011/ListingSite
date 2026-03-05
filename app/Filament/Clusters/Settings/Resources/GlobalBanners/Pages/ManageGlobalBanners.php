<?php

namespace App\Filament\Clusters\Settings\Resources\GlobalBanners\Pages;

use App\Filament\Clusters\Settings\Resources\GlobalBanners\GlobalBannerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageGlobalBanners extends ManageRecords
{
    protected static string $resource = GlobalBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Add Global Banner'),
        ];
    }
}
