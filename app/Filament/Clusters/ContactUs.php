<?php

namespace App\Filament\Clusters;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Facades\Filament;
use Filament\Support\Icons\Heroicon;

class ContactUs extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'Contact Us';

    protected static ?int $navigationSort = 6;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }
}
