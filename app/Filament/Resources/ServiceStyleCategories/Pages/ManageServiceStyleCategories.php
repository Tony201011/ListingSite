<?php

namespace App\Filament\Resources\ServiceStyleCategories\Pages;

use App\Filament\Resources\ServiceStyleCategories\ServiceStyleCategoryResource;
use App\Models\Category;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageServiceStyleCategories extends ManageRecords
{
    protected static string $resource = ServiceStyleCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['parent_id'] = $this->getServiceStyleParentId();
        $data['website_type'] = 'adult';

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['parent_id'] = $this->getServiceStyleParentId();
        $data['website_type'] = 'adult';

        return $data;
    }

    protected function getServiceStyleParentId(): int
    {
        return Category::query()->firstOrCreate(
            ['slug' => 'services-style'],
            [
                'name' => 'Services & style',
                'website_type' => 'adult',
                'sort_order' => 300,
                'is_active' => true,
            ],
        )->id;
    }
}
