<?php

namespace App\Filament\Clusters\Settings\Resources\TwilioSettings\Pages;

use App\Filament\Clusters\Settings\Resources\TwilioSettings\TwilioSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTwilioSettings extends ListRecords
{
    protected static string $resource = TwilioSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
