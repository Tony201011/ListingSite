<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Facades\Filament;
use Filament\Pages\Page;

class ProviderDashboard extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Provider Dashboard';

    protected static ?string $title = 'Provider Dashboard';

    protected static ?string $slug = 'provider-dashboard';

    protected string $view = 'filament.pages.provider-dashboard';

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'provider';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'provider';
    }
}
