<?php

namespace App\Filament\Resources\HelpPages\Pages;

use App\Filament\Resources\HelpPages\HelpPageResource;
use App\Models\HelpPage;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageHelpPages extends ManageRecords
{
    protected static string $resource = HelpPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Help Content')
                ->createAnother(false)
                ->visible(fn (): bool => HelpPage::query()->doesntExist()),
        ];
    }
}
