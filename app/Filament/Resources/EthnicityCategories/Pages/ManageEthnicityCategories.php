<?php

namespace App\Filament\Resources\EthnicityCategories\Pages;

use App\Filament\Resources\EthnicityCategories\EthnicityCategoryResource;
use App\Models\Category;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageEthnicityCategories extends ManageRecords
{
    protected static string $resource = EthnicityCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['parent_id'] = $this->getEthnicityParentId();
        $data['website_type'] = 'adult';

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['parent_id'] = $this->getEthnicityParentId();
        $data['website_type'] = 'adult';

        return $data;
    }

    protected function getEthnicityParentId(): int
    {
        return Category::query()->firstOrCreate(
            ['slug' => 'ethnicity'],
            [
                'name' => 'Ethnicity',
                'website_type' => 'adult',
                'sort_order' => 800,
                'is_active' => true,
            ],
        )->id;
    }
}
