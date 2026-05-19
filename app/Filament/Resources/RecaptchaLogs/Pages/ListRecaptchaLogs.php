<?php

namespace App\Filament\Resources\RecaptchaLogs\Pages;

use App\Filament\Resources\RecaptchaLogs\RecaptchaLogResource;
use App\Filament\Resources\Pages\ListRecordsWithPageJump;

class ListRecaptchaLogs extends ListRecordsWithPageJump
{
    protected static string $resource = RecaptchaLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
