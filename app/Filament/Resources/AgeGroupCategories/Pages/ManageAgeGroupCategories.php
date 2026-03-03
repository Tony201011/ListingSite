<?php

namespace App\Filament\Resources\AgeGroupCategories\Pages;

use App\Filament\Resources\AgeGroupCategories\AgeGroupCategoryResource;
use App\Models\Category;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAgeGroupCategories extends ManageRecords
{
    protected static string $resource = AgeGroupCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['parent_id'] = $this->getAgeGroupParentId();
        $data['website_type'] = 'adult';

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['parent_id'] = $this->getAgeGroupParentId();
        $data['website_type'] = 'adult';

        return $data;
    }

    protected function getAgeGroupParentId(): int
    {
        return Category::query()->firstOrCreate(
            ['slug' => 'age-group'],
            [
                'name' => 'Age Group',
                'website_type' => 'adult',
                'sort_order' => 500,
                'is_active' => true,
            ],
        )->id;
    }
}
