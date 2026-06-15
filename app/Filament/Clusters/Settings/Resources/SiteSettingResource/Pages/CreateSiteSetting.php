<?php

namespace App\Filament\Clusters\Settings\Resources\SiteSettingResource\Pages;

use App\Filament\Clusters\Settings\Resources\SiteSettingResource;
use App\Filament\Concerns\ReviewerReadOnly;
use Filament\Resources\Pages\CreateRecord;

class CreateSiteSetting extends CreateRecord
{
    use ReviewerReadOnly;

    protected static string $resource = SiteSettingResource::class;
}
