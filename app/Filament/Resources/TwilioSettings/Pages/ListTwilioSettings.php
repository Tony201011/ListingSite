<?php

namespace App\Filament\Resources\TwilioSettings\Pages;

use App\Filament\Resources\TwilioSettings\TwilioSettingResource;
use Filament\Actions\CreateAction;
use App\Filament\Resources\Pages\ListRecordsWithPageJump;

class ListTwilioSettings extends ListRecordsWithPageJump
{
    protected static string $resource = TwilioSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
