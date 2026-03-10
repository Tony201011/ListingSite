<?php

namespace App\Filament\Resources\GoogleRecaptchaSetting\Pages;

use App\Filament\Resources\GoogleRecaptchaSetting\GoogleRecaptchaSettingResource;
use App\Models\GoogleRecaptchaSetting;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageGoogleRecaptchaSettings extends ManageRecords
{
    protected static string $resource = GoogleRecaptchaSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Google Recaptcha Setting')
                ->createAnother(false)
                ->visible(fn (): bool => GoogleRecaptchaSetting::query()->doesntExist()),
        ];
    }
}
