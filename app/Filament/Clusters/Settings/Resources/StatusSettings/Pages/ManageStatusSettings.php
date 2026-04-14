<?php

namespace App\Filament\Clusters\Settings\Resources\StatusSettings\Pages;

use App\Filament\Clusters\Settings\Resources\SiteSettingResource;
use App\Filament\Clusters\Settings\Resources\StatusSettings\StatusSettingResource;
use App\Models\SiteSetting;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class ManageStatusSettings extends EditRecord
{
    protected static string $resource = StatusSettingResource::class;

    public function mount(int|string $record = null): void
    {
        $siteSetting = SiteSetting::first();

        if (! $siteSetting) {
            Notification::make()
                ->title('No site settings found. Please create site settings first.')
                ->warning()
                ->send();

            $this->redirect(SiteSettingResource::getUrl());

            return;
        }

        parent::mount($siteSetting->getKey());
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
