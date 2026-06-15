<?php

namespace App\Filament\Clusters\Settings\Resources\SiteSettingResource\Pages;

use App\Filament\Clusters\Settings\Resources\SiteSettingResource;
use App\Filament\Concerns\ReviewerReadOnly;
use Filament\Resources\Pages\EditRecord;

class EditSiteSetting extends EditRecord
{
    use ReviewerReadOnly;

    protected static string $resource = SiteSettingResource::class;
}
