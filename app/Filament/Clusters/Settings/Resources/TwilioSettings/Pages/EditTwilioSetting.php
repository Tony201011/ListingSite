<?php

namespace App\Filament\Clusters\Settings\Resources\TwilioSettings\Pages;

use App\Filament\Clusters\Settings\Resources\TwilioSettings\TwilioSettingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTwilioSetting extends EditRecord
{
    protected static string $resource = TwilioSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
