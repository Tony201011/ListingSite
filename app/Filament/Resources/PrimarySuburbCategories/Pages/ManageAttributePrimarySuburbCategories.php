<?php

namespace App\Filament\Resources\PrimarySuburbCategories\Pages;

use App\Filament\Resources\PrimarySuburbCategories\PrimarySuburbCategoriesResource; // Fixed import
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAttributePrimarySuburbCategories extends ManageRecords
{
    protected static string $resource = PrimarySuburbCategoriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    // Removed duplicate mutateFormDataBeforeCreate and mutateFormDataBeforeSave
    // These are now handled by the resource's static methods.
}
