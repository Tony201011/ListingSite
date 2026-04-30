<?php

namespace App\Filament\Resources\RecaptchaLogs\Pages;

use App\Filament\Resources\RecaptchaLogs\RecaptchaLogResource;
use Filament\Resources\Pages\ListRecords;

class ListRecaptchaLogs extends ListRecords
{
    protected static string $resource = RecaptchaLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
