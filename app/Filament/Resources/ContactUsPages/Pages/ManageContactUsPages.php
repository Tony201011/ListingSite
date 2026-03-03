<?php

namespace App\Filament\Resources\ContactUsPages\Pages;

use App\Filament\Resources\ContactUsPages\ContactUsPageResource;
use App\Models\ContactUsPage;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageContactUsPages extends ManageRecords
{
    protected static string $resource = ContactUsPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Contact Us Content')
                ->createAnother(false)
                ->visible(fn (): bool => ContactUsPage::query()->doesntExist()),
        ];
    }
}
