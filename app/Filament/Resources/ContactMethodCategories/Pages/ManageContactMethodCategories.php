<?php

namespace App\Filament\Resources\ContactMethodCategories\Pages;

use App\Filament\Resources\ContactMethodCategories\ContactMethodCategoryResource;
use App\Models\Category;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageContactMethodCategories extends ManageRecords
{
    protected static string $resource = ContactMethodCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['parent_id'] = $this->getContactMethodParentId();
        $data['website_type'] = 'adult';

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['parent_id'] = $this->getContactMethodParentId();
        $data['website_type'] = 'adult';

        return $data;
    }

    protected function getContactMethodParentId(): int
    {
        return Category::query()->firstOrCreate(
            ['slug' => 'contact-method'],
            [
                'name' => 'How can people contact you?',
                'website_type' => 'adult',
                'sort_order' => 1300,
                'is_active' => true,
            ],
        )->id;
    }
}
