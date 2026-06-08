<?php

namespace App\Filament\Resources\HowCreditsWorkPages\Pages;

use App\Filament\Resources\HowCreditsWorkPages\HowCreditsWorkPageResource;
use App\Models\HowCreditsWorkPage;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageHowCreditsWorkPages extends ManageRecords
{
    protected static string $resource = HowCreditsWorkPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add How Credits Work Page')
                ->createAnother(false)
                ->visible(fn (): bool => HowCreditsWorkPage::query()->doesntExist()),
        ];
    }
}
