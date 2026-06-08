<?php

namespace App\Filament\Resources\ContentModerationPolicies\Pages;

use App\Filament\Resources\ContentModerationPolicies\ContentModerationPolicyResource;
use App\Models\ContentModerationPolicy;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageContentModerationPolicies extends ManageRecords
{
    protected static string $resource = ContentModerationPolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Content Moderation Policy')
                ->createAnother(false)
                ->visible(fn (): bool => ContentModerationPolicy::query()->doesntExist()),
        ];
    }
}
