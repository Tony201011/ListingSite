<?php

namespace App\Filament\Resources\SmtpSettings\Pages;

use App\Filament\Resources\SmtpSettings\SmtpSettingResource;
use App\Models\SmtpSetting;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSmtpSettings extends ManageRecords
{
    protected static string $resource = SmtpSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add SMTP Setting')
                ->createAnother(false)
                ->visible(fn (): bool => SmtpSetting::query()->doesntExist()),
        ];
    }
}