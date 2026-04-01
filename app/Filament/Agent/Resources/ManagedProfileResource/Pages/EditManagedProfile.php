<?php

namespace App\Filament\Agent\Resources\ManagedProfileResource\Pages;

use App\Filament\Agent\Resources\ManagedProfileResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditManagedProfile extends EditRecord
{
    protected static string $resource = ManagedProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
