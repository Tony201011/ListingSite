<?php

namespace App\Filament\Resources\TwilioSettings\Pages;

use App\Filament\Concerns\ReviewerReadOnly;
use App\Filament\Resources\TwilioSettings\TwilioSettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTwilioSetting extends CreateRecord
{
    use ReviewerReadOnly;

    protected static string $resource = TwilioSettingResource::class;
}
