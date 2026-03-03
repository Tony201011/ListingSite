<?php

namespace App\Filament\Resources\YourLengthCategories\Pages;

use App\Filament\Resources\YourLengthCategories\YourLengthCategoryResource;
use App\Models\Category;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageYourLengthCategories extends ManageRecords
{
    protected static string $resource = YourLengthCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['parent_id'] = $this->getYourLengthParentId();
        $data['website_type'] = 'adult';

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['parent_id'] = $this->getYourLengthParentId();
        $data['website_type'] = 'adult';

        return $data;
    }

    protected function getYourLengthParentId(): int
    {
        return Category::query()->firstOrCreate(
            ['slug' => 'your-length'],
            [
                'name' => 'Your length',
                'website_type' => 'adult',
                'sort_order' => 1100,
                'is_active' => true,
            ],
        )->id;
    }
}
