<?php

namespace App\Filament\Clusters\Settings\Resources\HeaderWidgets\Pages;

use App\Filament\Clusters\Settings\Resources\HeaderWidgets\HeaderWidgetResource;
use App\Models\HeaderWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageHeaderWidgets extends ManageRecords
{
    protected static string $resource = HeaderWidgetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Header Widgets')
                ->slideOver()
                ->createAnother(false)
                ->visible(fn (): bool => HeaderWidget::query()->doesntExist()),
        ];
    }
}
