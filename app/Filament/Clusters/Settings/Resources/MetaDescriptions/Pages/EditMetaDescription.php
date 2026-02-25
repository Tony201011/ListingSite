<?php

namespace App\Filament\Clusters\Settings\Resources\MetaDescriptions\Pages;

use App\Filament\Clusters\Settings\Resources\MetaDescriptions\MetaDescriptionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditMetaDescription extends EditRecord
{
    protected static string $resource = MetaDescriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
