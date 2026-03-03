<?php

namespace App\Filament\Clusters\Settings\Resources\FooterTexts\Pages;

use App\Filament\Clusters\Settings\Resources\FooterTexts\FooterTextResource;
use App\Models\FooterText;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageFooterTexts extends ManageRecords
{
    protected static string $resource = FooterTextResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Footer Text')
                ->createAnother(false)
                ->visible(fn (): bool => FooterText::query()->doesntExist()),
        ];
    }
}
