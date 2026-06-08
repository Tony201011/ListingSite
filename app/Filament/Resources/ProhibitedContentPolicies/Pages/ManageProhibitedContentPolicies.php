<?php

namespace App\Filament\Resources\ProhibitedContentPolicies\Pages;

use App\Filament\Resources\ProhibitedContentPolicies\ProhibitedContentPolicyResource;
use App\Models\ProhibitedContentPolicy;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageProhibitedContentPolicies extends ManageRecords
{
    protected static string $resource = ProhibitedContentPolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Prohibited Content/Services Policy')
                ->createAnother(false)
                ->visible(fn (): bool => ProhibitedContentPolicy::query()->doesntExist()),
        ];
    }
}
