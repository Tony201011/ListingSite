<?php

namespace App\Filament\Clusters\Settings\Resources\TwilioSettings\Pages;

use App\Filament\Clusters\Settings\Resources\TwilioSettings\TwilioSettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTwilioSetting extends CreateRecord
{
    protected static string $resource = TwilioSettingResource::class;
}
