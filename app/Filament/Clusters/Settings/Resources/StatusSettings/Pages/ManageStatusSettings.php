<?php

namespace App\Filament\Clusters\Settings\Resources\StatusSettings\Pages;

use App\Filament\Clusters\Settings\Resources\SiteSettingResource;
use App\Filament\Clusters\Settings\Resources\StatusSettings\StatusSettingResource;
use App\Models\SiteSetting;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class ManageStatusSettings extends EditRecord
{
    protected static string $resource = StatusSettingResource::class;

    /**
     * The $record parameter is intentionally ignored; this page always loads the
     * singleton SiteSetting record so no record ID is required in the URL.
     */
    public function mount(int|string $record = null): void
    {
        $siteSetting = SiteSetting::first();

        if (! $siteSetting) {
            Notification::make()
                ->title('No site settings found. Please create site settings first.')
                ->warning()
                ->send();

            // redirect() sets the Livewire redirect flag but does not halt execution,
            // so we return early to prevent parent::mount() from running without a record.
            $this->redirect(SiteSettingResource::getUrl());

            return;
        }

        parent::mount($siteSetting->getKey());
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Status Settings')
                ->createAnother(false)
                ->visible(fn (): bool => SiteSetting::query()->doesntExist()),
        ];
    }
}
