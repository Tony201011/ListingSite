<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Accounts\AccountResource;
use App\Filament\Resources\SoftDeletedAccounts\SoftDeletedAccountResource;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AccountStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Account Insights';

    protected static ?int $sort = 2;

    protected static bool $isLazy = true;

    protected ?string $pollingInterval = '30s';

    public static function canView(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $providerAccounts = User::query()->where('role', User::ROLE_PROVIDER);
        $providerAccountsWithTrashed = User::query()
            ->withTrashed()
            ->where('role', User::ROLE_PROVIDER);

        $totalAccounts = (clone $providerAccountsWithTrashed)->count();
        $activeAccounts = (clone $providerAccounts)
            ->where('account_status', 'active')
            ->where('is_blocked', false)
            ->count();
        $inactiveAccounts = (clone $providerAccounts)
            ->where('account_status', 'inactive')
            ->where('is_blocked', false)
            ->count();
        $softDeletedAccounts = (clone $providerAccountsWithTrashed)
            ->onlyTrashed()
            ->count();
        $anonymizedAccounts = (clone $providerAccounts)
            ->where('account_status', 'anonymized')
            ->count();
        $blockedAccounts = (clone $providerAccounts)
            ->where('is_blocked', true)
            ->count();
        $accountsUrl = fn (array $filters = []): string => AccountResource::getUrl('index', [
            'filters' => $filters,
        ]);

        return [
            Stat::make('Total Accounts', (string) $totalAccounts)
                ->color('primary')
                ->icon('heroicon-o-users')
                ->url($accountsUrl()),
            Stat::make('Active', (string) $activeAccounts)
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->url($accountsUrl(['account_status' => ['value' => 'active']])),
            Stat::make('Inactive', (string) $inactiveAccounts)
                ->color('warning')
                ->icon('heroicon-o-pause-circle')
                ->url($accountsUrl(['account_status' => ['value' => 'inactive']])),
            Stat::make('Soft Deleted', (string) $softDeletedAccounts)
                ->color('gray')
                ->icon('heroicon-o-trash')
                ->url(SoftDeletedAccountResource::getUrl('index')),
            Stat::make('Anonymized', (string) $anonymizedAccounts)
                ->color('gray')
                ->icon('heroicon-o-eye-slash')
                ->url($accountsUrl(['account_status' => ['value' => 'anonymized']])),
            Stat::make('Blocked', (string) $blockedAccounts)
                ->color('danger')
                ->icon('heroicon-o-no-symbol')
                ->url($accountsUrl(['is_blocked' => ['value' => '1']])),
        ];
    }

    protected function getDescription(): ?string
    {
        return 'Quick overview of provider accounts';
    }
}
