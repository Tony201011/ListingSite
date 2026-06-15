<?php

namespace App\Filament\Clusters\Settings\Resources\CookieSettingResource\Pages;

use App\Filament\Clusters\Settings\Resources\CookieSettingResource;
use App\Filament\Concerns\ReviewerReadOnly;
use Filament\Resources\Pages\EditRecord;

class EditCookieSetting extends EditRecord
{
    use ReviewerReadOnly;

    protected static string $resource = CookieSettingResource::class;
}
