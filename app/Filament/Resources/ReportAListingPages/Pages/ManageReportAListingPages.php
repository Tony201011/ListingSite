<?php

namespace App\Filament\Resources\ReportAListingPages\Pages;

use App\Filament\Resources\ReportAListingPages\ReportAListingPageResource;
use App\Models\ReportAListingPage;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageReportAListingPages extends ManageRecords
{
    protected static string $resource = ReportAListingPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Report a Listing Page')
                ->createAnother(false)
                ->visible(fn (): bool => ReportAListingPage::query()->doesntExist()),
        ];
    }
}
