<?php

namespace App\Filament\Resources\NaughtyCornerPages\Pages;

use App\Filament\Resources\NaughtyCornerPages\NaughtyCornerPageResource;
use App\Models\NaughtyCornerPage;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageNaughtyCornerPages extends ManageRecords
{
    protected static string $resource = NaughtyCornerPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Naughty Corner Content')
                ->createAnother(false)
                ->visible(fn (): bool => NaughtyCornerPage::query()->doesntExist()),
        ];
    }
}
