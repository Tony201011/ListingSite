<?php

namespace App\Filament\Resources\HairLengthCategories\Pages;

use App\Filament\Resources\HairLengthCategories\HairLengthCategoryResource;
use App\Models\Category;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageHairLengthCategories extends ManageRecords
{
    protected static string $resource = HairLengthCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['parent_id'] = $this->getHairLengthParentId();
        $data['website_type'] = 'adult';

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['parent_id'] = $this->getHairLengthParentId();
        $data['website_type'] = 'adult';

        return $data;
    }

    protected function getHairLengthParentId(): int
    {
        return Category::query()->firstOrCreate(
            ['slug' => 'hair-length'],
            [
                'name' => 'Hair length',
                'website_type' => 'adult',
                'sort_order' => 700,
                'is_active' => true,
            ],
        )->id;
    }
}
