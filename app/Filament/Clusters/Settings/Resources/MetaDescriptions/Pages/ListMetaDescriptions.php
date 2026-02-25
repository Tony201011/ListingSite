<?php

namespace App\Filament\Clusters\Settings\Resources\MetaDescriptions\Pages;

use App\Filament\Clusters\Settings\Resources\MetaDescriptions\MetaDescriptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMetaDescriptions extends ListRecords
{
    protected static string $resource = MetaDescriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
