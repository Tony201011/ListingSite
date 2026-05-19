<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Filament\Widgets\ProviderStatsOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListUsers extends ListRecords
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

    protected function getTableContentFooter(): ?View
    {
        if (! isset($this->table)) {
            return null;
        }

        $recordsPerPage = $this->getTableRecordsPerPage();

        if (! is_numeric($recordsPerPage) || ((int) $recordsPerPage < 1)) {
            return null;
        }

        $lastPage = (int) ceil($this->getAllTableRecordsCount() / ((int) $recordsPerPage));

        if ($lastPage <= 1) {
            return null;
        }

        $records = $this->getTableRecords();

        return view('filament.tables.provider-page-jump', [
            'currentPage' => method_exists($records, 'currentPage') ? max(1, (int) $records->currentPage()) : 1,
            'lastPage' => $lastPage,
            'pageName' => $this->getTablePaginationPageName(),
        ]);
    }
}
