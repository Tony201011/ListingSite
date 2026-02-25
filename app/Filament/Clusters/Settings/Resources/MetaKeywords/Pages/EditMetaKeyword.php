<?php

namespace App\Filament\Clusters\Settings\Resources\MetaKeywords\Pages;

use App\Filament\Clusters\Settings\Resources\MetaKeywords\MetaKeywordResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMetaKeyword extends EditRecord
{
    protected static string $resource = MetaKeywordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
