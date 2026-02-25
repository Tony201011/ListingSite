<?php

namespace App\Filament\Clusters\Settings\Resources\CookieSettingResource\Pages;

use App\Filament\Clusters\Settings\Resources\CookieSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCookieSettings extends ListRecords
{
    protected static string $resource = CookieSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
