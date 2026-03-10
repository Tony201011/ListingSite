<?php

namespace App\Filament\Resources\TwilioSettings\Pages;

use App\Filament\Resources\TwilioSettings\TwilioSettingResource;
use App\Models\TwilioSetting;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTwilioSettings extends ManageRecords
{
    protected static string $resource = TwilioSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Twilio Setting')
                ->createAnother(false)
                ->visible(fn (): bool => TwilioSetting::query()->doesntExist()),
        ];
    }
}
