<?php

namespace App\Filament\Resources\GenderTabs\Pages;

use App\Filament\Resources\GenderTabs\GenderTabResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageGenderTabs extends ManageRecords
{
    protected static string $resource = GenderTabResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
