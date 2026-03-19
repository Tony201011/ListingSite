<?php

namespace App\Filament\Resources\TourCityCategories\Pages;

use App\Filament\Resources\TourCityCategories\TourCityCategoriesResource; // Fixed import
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAttributeTourCity extends ManageRecords
{
    protected static string $resource = TourCityCategoriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    // Removed duplicate mutateFormDataBeforeCreate and mutateFormDataBeforeSave
    // These are now handled by the resource's static methods.
}
