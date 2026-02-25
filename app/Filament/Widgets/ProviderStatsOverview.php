<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProviderStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Provider Insights';

    protected ?string $description = 'Quick overview of provider accounts';

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $providers = User::query()->where('role', User::ROLE_PROVIDER);

        $total = (clone $providers)->count();
        $active = (clone $providers)->where('is_blocked', false)->count();
        $blocked = (clone $providers)->where('is_blocked', true)->count();
        $verified = (clone $providers)->whereNotNull('email_verified_at')->count();

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
}
