<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Accounts\AccountResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\ProviderProfile;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class AvailabilityStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Available Now Insights';

    protected static ?int $sort = 3;

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
        $applyAvailableNowScope = function (Builder $query): Builder {
            return $query->where(function (Builder $availabilityQuery): void {
                $availabilityQuery->whereCurrentlyOnline()
                    ->orWhere(fn (Builder $orQuery): Builder => $orQuery->whereCurrentlyAvailableNow());
            });
        };

        $availableAccounts = User::query()
            ->where('role', User::ROLE_PROVIDER)
            ->whereHas(
                'providerProfiles',
                fn (Builder $query): Builder => $applyAvailableNowScope($query->withoutTrashed())
            )
            ->count();

        $availableProviders = $applyAvailableNowScope(
            ProviderProfile::query()->withoutTrashed()
        )->count();

        return [
            Stat::make('Total Accounts Available Now', (string) $availableAccounts)
                ->color('success')
                ->icon('heroicon-o-users')
                ->url(AccountResource::getUrl('index')),
            Stat::make('Total Providers Available Now', (string) $availableProviders)
                ->color('warning')
                ->icon('heroicon-o-bolt')
                ->url(UserResource::getUrl('index', [
                    'tableFilters' => [
                        'available_now_status' => ['value' => 'online'],
                    ],
                ])),
        ];
    }

    protected function getDescription(): ?string
    {
        return 'Live available now totals for provider accounts and profiles';
    }
}
