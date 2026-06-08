<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Accounts\AccountResource;
use App\Filament\Resources\Users\UserResource;
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
        $totalAccounts = User::query()->count();
        $providerProfiles = ProviderProfile::query();
        $providerProfilesWithoutTrashed = ProviderProfile::query()->withoutTrashed();

        $activeProviders = (clone $providerProfilesWithoutTrashed)
            ->where('profile_status', 'approved')
            ->whereNull('anonymized_at')
            ->where('is_blocked', false)
            ->count();
        $inactiveProviders = (clone $providerProfilesWithoutTrashed)
            ->where('profile_status', '!=', 'approved')
            ->whereNull('anonymized_at')
            ->where('is_blocked', false)
            ->count();
        $softDeletedProviders = ProviderProfile::query()->onlyTrashed()->count();
        $anonymizedProviders = (clone $providerProfiles)->whereNotNull('anonymized_at')->count();
        $blockedProviders = (clone $providerProfilesWithoutTrashed)->where('is_blocked', true)->count();
        $totalProviders = (clone $providerProfiles)->withTrashed()->count();
        $availableNow = (clone $providerProfilesWithoutTrashed)->where(function ($query) {
            $query->whereCurrentlyOnline()
                ->orWhere(fn ($orQuery) => $orQuery->whereCurrentlyAvailableNow());
        })->count();

        $accountsUrl = fn (array $filters): string => AccountResource::getUrl('index', [
            'filters' => $filters,
        ]);
        $profilesUrl = fn (array $tableFilters): string => UserResource::getUrl('index', [
            'tableFilters' => $tableFilters,
        ]);

        return [
            Stat::make('Total Accounts', (string) $totalAccounts)
                ->color('primary')
                ->icon('heroicon-o-users')
                ->url($accountsUrl([])),
            Stat::make('Total Providers', (string) $totalProviders)
                ->color('info')
                ->icon('heroicon-o-identification')
                ->url($profilesUrl([])),
            Stat::make('Available Now', (string) $availableNow)
                ->color('success')
                ->icon('heroicon-o-bolt')
                ->url($profilesUrl(['available_now_status' => ['value' => 'online']])),
            Stat::make('Active', (string) $activeProviders)
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->url($profilesUrl(['profile_status' => ['value' => 'approved']])),
            Stat::make('Inactive', (string) $inactiveProviders)
                ->color('warning')
                ->icon('heroicon-o-pause-circle')
                ->url($profilesUrl(['profile_status' => ['value' => 'pending']])),
            Stat::make('Soft Deleted', (string) $softDeletedProviders)
                ->color('gray')
                ->icon('heroicon-o-trash')
                ->url($profilesUrl(['deleted_status' => ['value' => 'deleted']])),
            Stat::make('Anonymized', (string) $anonymizedProviders)
                ->color('gray')
                ->icon('heroicon-o-eye-slash')
                ->url($profilesUrl([])),
            Stat::make('Blocked', (string) $blockedProviders)
                ->color('danger')
                ->icon('heroicon-o-no-symbol')
                ->url($profilesUrl(['is_blocked' => ['value' => '1']])),
        ];
    }

    protected function getDescription(): ?string
    {
        return 'Quick overview of provider profiles and accounts';
    }
}
