<?php

namespace App\Filament\Resources\PhoneContactPreferenceCategories\Pages;

use App\Filament\Resources\PhoneContactPreferenceCategories\PhoneContactPreferenceCategoryResource;
use App\Models\Category;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePhoneContactPreferenceCategories extends ManageRecords
{
    protected static string $resource = PhoneContactPreferenceCategoryResource::class;

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
            ['slug' => 'phone-contact-preferences'],
            [
                'name' => 'Phone contact preferences',
                'website_type' => 'adult',
                'sort_order' => 1400,
                'is_active' => true,
            ],
        )->id;
    }
}
