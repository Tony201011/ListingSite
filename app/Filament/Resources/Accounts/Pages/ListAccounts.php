<?php

namespace App\Filament\Resources\Accounts\Pages;

use App\Filament\Resources\Accounts\AccountResource;
use App\Filament\Resources\Pages\ListRecordsWithPageJump;
use App\Filament\Widgets\ProviderStatsOverview;
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

    protected function getHeaderWidgets(): array
    {
        return [
            ProviderStatsOverview::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

}
