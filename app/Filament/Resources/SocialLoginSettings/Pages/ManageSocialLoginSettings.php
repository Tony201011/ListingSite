<?php

namespace App\Filament\Resources\SocialLoginSettings\Pages;

use App\Filament\Resources\SocialLoginSettings\SocialLoginSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSocialLoginSettings extends ManageRecords
{
    protected static string $resource = SocialLoginSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Provider Setting'),
        ];
    }
}