<?php

namespace App\Filament\Clusters\Settings\Resources\FooterWidgets\Pages;

use App\Filament\Clusters\Settings\Resources\FooterWidgets\FooterWidgetResource;
use App\Models\FooterWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageFooterWidgets extends ManageRecords
{
    protected static string $resource = FooterWidgetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Footer Widgets')
                ->slideOver()
                ->createAnother(false)
                ->visible(fn (): bool => FooterWidget::query()->doesntExist()),
        ];
    }
}
