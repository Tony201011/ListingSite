<?php

namespace App\Filament\Resources\AntiSpamPolicies\Pages;

use App\Filament\Resources\AntiSpamPolicies\AntiSpamPolicyResource;
use App\Models\AntiSpamPolicy;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAntiSpamPolicies extends ManageRecords
{
    protected static string $resource = AntiSpamPolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Anti Spam Policy')
                ->createAnother(false)
                ->visible(fn (): bool => AntiSpamPolicy::query()->doesntExist()),
        ];
    }
}