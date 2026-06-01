<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Widgets\AccountStatusChart;
use App\Filament\Widgets\AvailabilityChart;
use App\Filament\Widgets\FeaturedListingChart;
use App\Filament\Widgets\OnlineUsersChart;
use App\Filament\Widgets\PaymentPurchasesChart;
use App\Filament\Widgets\PaymentSalesChart;
use App\Filament\Widgets\PaymentStatsOverview;
use App\Filament\Widgets\ProfileStatusChart;
use App\Filament\Widgets\ProviderRegistrationsChart;
use App\Filament\Widgets\ProviderStatsOverview;
use App\Filament\Widgets\SiteVisitorsChart;
use App\Filament\Widgets\UniqueUsersChart;
use App\Filament\Widgets\VisitorStatsOverview;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class Dashboard extends BaseDashboard
{
    protected static ?string $slug = 'dashboard';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = 'Dashboard';

    protected static string|UnitEnum|null $navigationGroup = 'Dashboard';

    protected static ?int $navigationSort = 1;

    public function getWidgets(): array
    {
        return [
            VisitorStatsOverview::class,
            ProviderStatsOverview::class,
            ProviderRegistrationsChart::class,
            ProfileStatusChart::class,
            AccountStatusChart::class,
            SiteVisitorsChart::class,
            PaymentStatsOverview::class,
            UniqueUsersChart::class,
            PaymentSalesChart::class,
            PaymentPurchasesChart::class,
            OnlineUsersChart::class,
            AvailabilityChart::class,
            FeaturedListingChart::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }
}
