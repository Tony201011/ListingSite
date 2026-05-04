<?php

namespace App\Filament\Widgets;

use App\Models\ProviderProfile;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProviderStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Provider Insights';

    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $profiles = ProviderProfile::query()->withoutTrashed();
        $users = User::query()->where('role', User::ROLE_PROVIDER);

        $total = $profiles->count();
        $active = (clone $users)->where('is_blocked', false)->count();
        $blocked = (clone $users)->where('is_blocked', true)->count();
        $verified = (clone $users)->whereNotNull('email_verified_at')->count();

        return [
            Stat::make('Total Providers', (string) $total)
                ->color('primary')
                ->icon('heroicon-o-users'),
            Stat::make('Active Accounts', (string) $active)
                ->color('success')
                ->icon('heroicon-o-check-circle'),
            Stat::make('Blocked Accounts', (string) $blocked)
                ->color('danger')
                ->icon('heroicon-o-no-symbol'),
            Stat::make('Verified Emails', (string) $verified)
                ->color('warning')
                ->icon('heroicon-o-shield-check'),
        ];
    }

    protected function getDescription(): ?string
    {
        return 'Quick overview of provider profiles and accounts';
    }
}
