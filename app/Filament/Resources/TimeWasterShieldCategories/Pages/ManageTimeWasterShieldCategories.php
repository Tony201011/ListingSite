<?php

namespace App\Filament\Resources\TimeWasterShieldCategories\Pages;

use App\Filament\Resources\TimeWasterShieldCategories\TimeWasterShieldCategoryResource;
use App\Models\Category;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTimeWasterShieldCategories extends ManageRecords
{
    protected static string $resource = TimeWasterShieldCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['parent_id'] = $this->getParentId();
        $data['website_type'] = 'adult';

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['parent_id'] = $this->getParentId();
        $data['website_type'] = 'adult';

        return $data;
    }

    protected function getParentId(): int
    {
        return Category::query()->firstOrCreate(
            ['slug' => 'time-waster-shield'],
            [
                'name' => 'Use time waster shield for SMS?',
                'website_type' => 'adult',
                'sort_order' => 1500,
                'is_active' => true,
            ],
        )->id;
    }
}
