<?php

namespace App\Filament\Resources\TwilioSettings\Pages;

use App\Filament\Resources\TwilioSettings\TwilioSettingResource;
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
