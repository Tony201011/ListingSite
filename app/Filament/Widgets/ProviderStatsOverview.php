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
        $users = User::query()->where('role', User::ROLE_PROVIDER)->withoutTrashed();

        $total = $profiles->count();
        $active = (clone $users)->whereHas('providerProfiles', function ($q) {
            $q->where('profile_status', 'approved');
        })->count();
        $onlineCount = (clone $profiles)->whereHas('onlineUser', function ($q) {
            $q->where('status', 'online')
                ->where('online_expires_at', '>', now());
        })->count();
        $blocked = (clone $users)->where('is_blocked', true)->count();
        $verified = (clone $users)->whereNotNull('email_verified_at')->count();
        $featured = (clone $profiles)->where('is_featured', true)->count();

        return [
            Stat::make('Total Providers', (string) $total)
                ->color('primary')
                ->icon('heroicon-o-users'),
            Stat::make('Active Accounts', (string) $active)
                ->color('success')
                ->icon('heroicon-o-check-circle'),
            Stat::make('Online Users', (string) $onlineCount)
                ->color('success')
                ->icon('heroicon-o-signal'),
            Stat::make('Blocked Accounts', (string) $blocked)
                ->color('danger')
                ->icon('heroicon-o-no-symbol'),
            Stat::make('Verified Emails', (string) $verified)
                ->color('warning')
                ->icon('heroicon-o-shield-check'),
            Stat::make('Featured Profiles', (string) $featured)
                ->color('info')
                ->icon('heroicon-o-star'),
        ];
    }

    protected function getDescription(): ?string
    {
        return 'Quick overview of provider profiles and accounts';
    }
}
