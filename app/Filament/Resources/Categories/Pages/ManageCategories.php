<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Str;

class ManageCategories extends ManageRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Category')
                ->mutateDataUsing(function (array $data): array {
                    if (blank($data['slug'] ?? null) && filled($data['name'] ?? null)) {
                        $data['slug'] = Str::slug($data['name']);
                    }

                    return $data;
                }),
        ];
    }
}
