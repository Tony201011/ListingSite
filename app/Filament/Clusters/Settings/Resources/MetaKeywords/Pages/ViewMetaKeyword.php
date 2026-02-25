<?php

namespace App\Filament\Clusters\Settings\Resources\MetaKeywords\Pages;

use App\Filament\Clusters\Settings\Resources\MetaKeywords\MetaKeywordResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMetaKeyword extends ViewRecord
{
    protected static string $resource = MetaKeywordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
