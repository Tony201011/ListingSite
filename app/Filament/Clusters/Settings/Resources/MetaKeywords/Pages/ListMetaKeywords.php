<?php

namespace App\Filament\Clusters\Settings\Resources\MetaKeywords\Pages;

use App\Filament\Clusters\Settings\Resources\MetaKeywords\MetaKeywordResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMetaKeywords extends ListRecords
{
    protected static string $resource = MetaKeywordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
