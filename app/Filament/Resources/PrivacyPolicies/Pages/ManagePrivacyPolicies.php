<?php

namespace App\Filament\Resources\PrivacyPolicies\Pages;

use App\Filament\Resources\PrivacyPolicies\PrivacyPolicyResource;
use App\Models\PrivacyPolicy;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePrivacyPolicies extends ManageRecords
{
    protected static string $resource = PrivacyPolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Privacy Policy')
                ->createAnother(false)
                ->visible(fn (): bool => PrivacyPolicy::query()->doesntExist()),
        ];
    }
}