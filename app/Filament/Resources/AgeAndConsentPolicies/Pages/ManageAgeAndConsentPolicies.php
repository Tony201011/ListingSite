<?php

namespace App\Filament\Resources\AgeAndConsentPolicies\Pages;

use App\Filament\Resources\AgeAndConsentPolicies\AgeAndConsentPolicyResource;
use App\Models\AgeAndConsentPolicy;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAgeAndConsentPolicies extends ManageRecords
{
    protected static string $resource = AgeAndConsentPolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Age and Consent Policy')
                ->createAnother(false)
                ->visible(fn (): bool => AgeAndConsentPolicy::query()->doesntExist()),
        ];
    }
}
