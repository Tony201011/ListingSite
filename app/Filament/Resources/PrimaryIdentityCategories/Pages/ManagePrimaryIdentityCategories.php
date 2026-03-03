<?php

namespace App\Filament\Resources\PrimaryIdentityCategories\Pages;

use App\Filament\Resources\PrimaryIdentityCategories\PrimaryIdentityCategoryResource;
use App\Models\Category;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePrimaryIdentityCategories extends ManageRecords
{
    protected static string $resource = PrimaryIdentityCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['parent_id'] = $this->getPrimaryIdentityParentId();
        $data['website_type'] = 'adult';

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['parent_id'] = $this->getPrimaryIdentityParentId();
        $data['website_type'] = 'adult';

        return $data;
    }

    protected function getPrimaryIdentityParentId(): int
    {
        return Category::query()->firstOrCreate(
            ['slug' => 'primary-identity'],
            [
                'name' => 'Primary identity',
                'website_type' => 'adult',
                'sort_order' => 100,
                'is_active' => true,
            ],
        )->id;
    }
}
