<?php

namespace App\Filament\Agent\Resources\ManagedProfileResource\Pages;

use App\Filament\Agent\Resources\ManagedProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListManagedProfiles extends ListRecords
{
    protected static string $resource = ManagedProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Add Profile'),
        ];
    }
}
