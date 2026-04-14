<?php

namespace App\Filament\Resources\BabeRankReadMorePages\Pages;

use App\Filament\Resources\BabeRankReadMorePages\BabeRankReadMorePageResource;
use App\Models\BabeRankReadMorePage;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageBabeRankReadMorePages extends ManageRecords
{
    protected static string $resource = BabeRankReadMorePageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Babe Rank Read More Content')
                ->createAnother(false)
                ->visible(fn (): bool => BabeRankReadMorePage::query()->doesntExist()),
        ];
    }
}
