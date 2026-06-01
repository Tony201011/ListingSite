<?php

namespace App\Filament\Resources\Pages;

use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

abstract class ListRecordsWithPageJump extends ListRecords
{
    protected function resetTableToFirstPage(): void
    {
        $this->resetPage($this->getTablePaginationPageName());
    }

    public function updatedTableFilters(): void
    {
        $this->resetTableToFirstPage();
    }

    public function updatedTableSearch(): void
    {
        $this->resetTableToFirstPage();
    }

    public function updatedTableColumnSearches($value = null, ?string $key = null): void
    {
        $this->resetTableToFirstPage();
    }

    public function updatedTableGrouping(): void
    {
        $this->resetTableToFirstPage();
    }

    protected function getTableContentFooter(): ?View
    {
        if (! isset($this->table)) {
            return null;
        }

        $records = $this->getTableRecords();
        $currentPage = method_exists($records, 'currentPage') ? max(1, (int) $records->currentPage()) : 1;
        $lastPage = method_exists($records, 'lastPage') ? max(1, (int) $records->lastPage()) : 1;

        if ($lastPage <= 1) {
            $recordsPerPage = $this->getTableRecordsPerPage();

            if (is_numeric($recordsPerPage) && ((int) $recordsPerPage > 0)) {
                $lastPage = (int) ceil($this->getAllTableRecordsCount() / ((int) $recordsPerPage));
            }
        }

        if ($lastPage <= 1) {
            return null;
        }

        return view('filament.tables.provider-page-jump', [
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
            'pageName' => $this->getTablePaginationPageName(),
        ]);
    }
}
