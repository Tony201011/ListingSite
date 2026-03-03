<?php

namespace App\Filament\Resources\BodyTypeCategories\Pages;

use App\Filament\Resources\BodyTypeCategories\BodyTypeCategoryResource;
use App\Models\Category;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageBodyTypeCategories extends ManageRecords
{
    protected static string $resource = BodyTypeCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['parent_id'] = $this->getBodyTypeParentId();
        $data['website_type'] = 'adult';

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['parent_id'] = $this->getBodyTypeParentId();
        $data['website_type'] = 'adult';

        return $data;
    }

    protected function getBodyTypeParentId(): int
    {
        return Category::query()->firstOrCreate(
            ['slug' => 'body-type'],
            [
                'name' => 'Body type',
                'website_type' => 'adult',
                'sort_order' => 900,
                'is_active' => true,
            ],
        )->id;
    }
}
