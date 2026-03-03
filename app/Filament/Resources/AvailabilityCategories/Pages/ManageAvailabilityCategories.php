<?php

namespace App\Filament\Resources\AvailabilityCategories\Pages;

use App\Filament\Resources\AvailabilityCategories\AvailabilityCategoryResource;
use App\Models\Category;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAvailabilityCategories extends ManageRecords
{
    protected static string $resource = AvailabilityCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['parent_id'] = $this->getAvailabilityParentId();
        $data['website_type'] = 'adult';

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['parent_id'] = $this->getAvailabilityParentId();
        $data['website_type'] = 'adult';

        return $data;
    }

    protected function getAvailabilityParentId(): int
    {
        return Category::query()->firstOrCreate(
            ['slug' => 'availability'],
            [
                'name' => 'Are you available for',
                'website_type' => 'adult',
                'sort_order' => 1200,
                'is_active' => true,
            ],
        )->id;
    }
}
