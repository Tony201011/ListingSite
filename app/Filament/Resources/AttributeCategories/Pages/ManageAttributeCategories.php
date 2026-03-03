<?php

namespace App\Filament\Resources\AttributeCategories\Pages;

use App\Filament\Resources\AttributeCategories\AttributeCategoryResource;
use App\Models\Category;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAttributeCategories extends ManageRecords
{
    protected static string $resource = AttributeCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['parent_id'] = $this->getAttributesParentId();
        $data['website_type'] = 'adult';

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['parent_id'] = $this->getAttributesParentId();
        $data['website_type'] = 'adult';

        return $data;
    }

    protected function getAttributesParentId(): int
    {
        return Category::query()->firstOrCreate(
            ['slug' => 'attributes'],
            [
                'name' => 'Attributes',
                'website_type' => 'adult',
                'sort_order' => 200,
                'is_active' => true,
            ],
        )->id;
    }
}
