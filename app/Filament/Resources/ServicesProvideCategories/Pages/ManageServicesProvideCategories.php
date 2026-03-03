<?php

namespace App\Filament\Resources\ServicesProvideCategories\Pages;

use App\Filament\Resources\ServicesProvideCategories\ServicesProvideCategoryResource;
use App\Models\Category;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageServicesProvideCategories extends ManageRecords
{
    protected static string $resource = ServicesProvideCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['parent_id'] = $this->getServicesProvideParentId();
        $data['website_type'] = 'adult';

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['parent_id'] = $this->getServicesProvideParentId();
        $data['website_type'] = 'adult';

        return $data;
    }

    protected function getServicesProvideParentId(): int
    {
        return Category::query()->firstOrCreate(
            ['slug' => 'services-you-provide'],
            [
                'name' => 'Services you provide',
                'website_type' => 'adult',
                'sort_order' => 400,
                'is_active' => true,
            ],
        )->id;
    }
}
