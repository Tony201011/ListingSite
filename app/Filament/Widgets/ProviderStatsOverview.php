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
        $onlineCount = (clone $profiles)->whereCurrentlyOnline()->count();
        $blocked = (clone $profiles)->where('is_blocked', true)->count();
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
                ->color('info')
                ->icon('heroicon-o-signal')
                ->url(fn (): string => route('filament.admin.resources.providers.index', [
                    'filters' => ['online_status' => ['value' => 'online']],
                ])),
            Stat::make('Blocked Accounts', (string) $blocked)
                ->color('danger')
                ->icon('heroicon-o-no-symbol')
                ->url(fn (): string => route('filament.admin.resources.providers.index', [
                    'filters' => ['is_blocked' => ['value' => '1']],
                ])),
            Stat::make('Verified Emails', (string) $verified)
                ->color('warning')
                ->icon('heroicon-o-shield-check'),
            Stat::make('Featured Profiles', (string) $featured)
                ->color('info')
                ->icon('heroicon-o-star')
                ->url(fn (): string => route('filament.admin.resources.providers.index', [
                    'filters' => ['is_featured' => ['value' => '1']],
                ])),
        ];
    }

    protected function getDescription(): ?string
    {
        return 'Quick overview of provider profiles and accounts';
    }
}
