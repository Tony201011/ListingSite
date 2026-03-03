<?php

namespace App\Filament\Resources\HairColorCategories\Pages;

use App\Filament\Resources\HairColorCategories\HairColorCategoryResource;
use App\Models\Category;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageHairColorCategories extends ManageRecords
{
    protected static string $resource = HairColorCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['parent_id'] = $this->getHairColorParentId();
        $data['website_type'] = 'adult';

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['parent_id'] = $this->getHairColorParentId();
        $data['website_type'] = 'adult';

        return $data;
    }

    protected function getHairColorParentId(): int
    {
        return Category::query()->firstOrCreate(
            ['slug' => 'hair-color'],
            [
                'name' => 'Hair color',
                'website_type' => 'adult',
                'sort_order' => 600,
                'is_active' => true,
            ],
        )->id;
    }
}
