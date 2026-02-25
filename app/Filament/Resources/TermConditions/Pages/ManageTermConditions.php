<?php

namespace App\Filament\Resources\TermConditions\Pages;

use App\Filament\Resources\TermConditions\TermConditionResource;
use App\Models\TermCondition;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTermConditions extends ManageRecords
{
    protected static string $resource = TermConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Terms')
                ->createAnother(false)
                ->visible(fn (): bool => TermCondition::query()->doesntExist()),
        ];
    }
}