<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Filament\Widgets\ProviderStatsOverview;
use Filament\Actions\CreateAction;
use App\Filament\Resources\Pages\ListRecordsWithPageJump;

class ListUsers extends ListRecordsWithPageJump
{
    protected static string $resource = UserResource::class;

    public function mount(): void
    {
        parent::mount();

        if ($this->tableFilters !== null) {
            return;
        }

        $legacyTableFilters = request()->query('tableFilters');

        if (! is_array($legacyTableFilters)) {
            return;
        }

        $this->normalizeTableFilterValuesFromQueryString($legacyTableFilters);
        $this->tableFilters = $legacyTableFilters;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Create Provider'),
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
