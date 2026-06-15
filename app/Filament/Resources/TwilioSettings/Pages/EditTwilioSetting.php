<?php

namespace App\Filament\Resources\TwilioSettings\Pages;

use App\Filament\Concerns\ReviewerReadOnly;
use App\Filament\Resources\TwilioSettings\TwilioSettingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTwilioSetting extends EditRecord
{
    use ReviewerReadOnly;

    protected static string $resource = TwilioSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
