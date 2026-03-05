<?php

namespace App\Filament\Resources\AboutUsPages\Pages;

use App\Filament\Resources\AboutUsPages\AboutUsPageResource;
use App\Models\AboutUsPage;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAboutUsPages extends ManageRecords
{
    protected static string $resource = AboutUsPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add About Us Content')
                ->createAnother(false)
                ->visible(fn (): bool => AboutUsPage::query()->doesntExist()),
        ];
    }
}
