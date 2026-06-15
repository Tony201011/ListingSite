<?php

namespace App\Filament\Clusters\Settings\Resources\CookieSettingResource\Pages;

use App\Filament\Clusters\Settings\Resources\CookieSettingResource;
use App\Filament\Concerns\ReviewerReadOnly;
use Filament\Resources\Pages\CreateRecord;

class CreateCookieSetting extends CreateRecord
{
    use ReviewerReadOnly;

    protected static string $resource = CookieSettingResource::class;
}
