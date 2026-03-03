<?php

namespace App\Filament\Resources\BustSizeCategories\Pages;

use App\Filament\Resources\BustSizeCategories\BustSizeCategoryResource;
use App\Models\Category;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageBustSizeCategories extends ManageRecords
{
    protected static string $resource = BustSizeCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['parent_id'] = $this->getBustSizeParentId();
        $data['website_type'] = 'adult';

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['parent_id'] = $this->getBustSizeParentId();
        $data['website_type'] = 'adult';

        return $data;
    }

    protected function getBustSizeParentId(): int
    {
        return Category::query()->firstOrCreate(
            ['slug' => 'bust-size'],
            [
                'name' => 'Bust size',
                'website_type' => 'adult',
                'sort_order' => 1000,
                'is_active' => true,
            ],
        )->id;
    }
}
