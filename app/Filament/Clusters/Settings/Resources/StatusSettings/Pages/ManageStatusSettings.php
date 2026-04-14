<?php

namespace App\Filament\Clusters\Settings\Resources\StatusSettings\Pages;

use App\Filament\Clusters\Settings\Resources\StatusSettings\StatusSettingResource;
use App\Models\SiteSetting;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageStatusSettings extends ManageRecords
{
    protected static string $resource = StatusSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Status Settings')
                ->createAnother(false)
                ->visible(fn (): bool => SiteSetting::query()->doesntExist()),
        ];
    }
}
