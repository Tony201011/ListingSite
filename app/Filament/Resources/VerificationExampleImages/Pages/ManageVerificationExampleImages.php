<?php

namespace App\Filament\Resources\VerificationExampleImages\Pages;

use App\Filament\Resources\VerificationExampleImages\VerificationExampleImageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageVerificationExampleImages extends ManageRecords
{
    protected static string $resource = VerificationExampleImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Example Image'),
        ];
    }
}
