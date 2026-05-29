<?php

namespace App\Filament\Resources\Accounts\Pages;

use App\Filament\Resources\Accounts\AccountResource;
use App\Filament\Resources\Pages\ListRecordsWithPageJump;
use Filament\Actions\CreateAction;

class ListAccounts extends ListRecordsWithPageJump
{
    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Create Account'),
        ];
    }
}

