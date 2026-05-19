<?php

namespace App\Filament\Resources\Pages;

use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

abstract class ListRecordsWithPageJump extends ListRecords
{
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
