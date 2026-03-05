<?php

namespace App\Filament\Resources\PricingPages\Pages;

use App\Filament\Resources\PricingPages\PricingPageResource;
use App\Models\PricingPage;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePricingPages extends ManageRecords
{
    protected static string $resource = PricingPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Pricing Page')
                ->createAnother(false)
                ->visible(fn (): bool => PricingPage::query()->doesntExist()),
        ];
    }
}
