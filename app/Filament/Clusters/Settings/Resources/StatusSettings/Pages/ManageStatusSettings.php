<?php

namespace App\Filament\Clusters\Settings\Resources\StatusSettings\Pages;

use App\Filament\Clusters\Settings\Resources\StatusSettings\StatusSettingResource;
use Filament\Resources\Pages\ManageRecords;

class ManageStatusSettings extends ManageRecords
{
    protected static string $resource = StatusSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
