<?php

namespace App\Filament\Resources\FaqPages\Pages;

use App\Filament\Resources\FaqPages\FaqPageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageFaqPages extends ManageRecords
{
    protected static string $resource = FaqPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add FAQ Page Settings'),
        ];
    }
}
