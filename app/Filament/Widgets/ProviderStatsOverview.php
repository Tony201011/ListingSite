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
        $users = User::query()->where('role', User::ROLE_PROVIDER)->withoutTrashed();
        $profiles = ProviderProfile::query()->whereCurrentlyAvailableNow();

        $total = (clone $users)->count();
        $active = (clone $users)->where('account_status', 'active')->count();
        $inactive = (clone $users)->where('account_status', 'inactive')->count();
        $softDeleted = (clone $users)->where('account_status', 'soft_deleted')->count();
        $anonymized = (clone $users)->where('account_status', 'anonymized')->count();
        $blocked = (clone $users)->where('is_blocked', true)->count();
        $availableNow = (clone $profiles)->count();

        $accountsUrl = fn (array $filters): string => AccountResource::getUrl('index', [
            'filters' => $filters,
        ]);
        $profilesUrl = fn (array $tableFilters): string => UserResource::getUrl('index', [
            'tableFilters' => $tableFilters,
        ]);

        return [
            Stat::make('Total Accounts', (string) $total)
                ->color('primary')
                ->icon('heroicon-o-users')
                ->url($accountsUrl([])),
            Stat::make('Available Now', (string) $availableNow)
                ->color('success')
                ->icon('heroicon-o-bolt')
                ->url($profilesUrl([])),
            Stat::make('Active', (string) $active)
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->url($accountsUrl(['account_status' => ['value' => 'active']])),
            Stat::make('Inactive', (string) $inactive)
                ->color('warning')
                ->icon('heroicon-o-pause-circle')
                ->url($accountsUrl(['account_status' => ['value' => 'inactive']])),
            Stat::make('Soft Deleted', (string) $softDeleted)
                ->color('gray')
                ->icon('heroicon-o-trash')
                ->url($accountsUrl(['account_status' => ['value' => 'soft_deleted']])),
            Stat::make('Anonymized', (string) $anonymized)
                ->color('gray')
                ->icon('heroicon-o-eye-slash')
                ->url($accountsUrl(['account_status' => ['value' => 'anonymized']])),
            Stat::make('Blocked', (string) $blocked)
                ->color('danger')
                ->icon('heroicon-o-no-symbol')
                ->url($accountsUrl(['is_blocked' => ['value' => '1']])),
        ];
    }

    protected function getDescription(): ?string
    {
        return 'Quick overview of provider profiles and accounts';
    }
}
