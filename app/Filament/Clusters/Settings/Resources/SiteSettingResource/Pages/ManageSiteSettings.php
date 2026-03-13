<?php

namespace App\Filament\Clusters\Settings\Resources\SiteSettingResource\Pages;

use App\Filament\Clusters\Settings\Resources\SiteSettingResource;
use App\Models\SiteSetting;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSiteSettings extends ManageRecords
{
    protected static string $resource = SiteSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Site Setting')
                ->createAnother(false)
                ->visible(fn (): bool => SiteSetting::query()->doesntExist()),
        ];
    }
}
