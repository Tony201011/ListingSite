<?php

namespace App\Filament\Widgets;

use App\Models\PurchaseTransaction;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class PaymentStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Payment Overview';

    protected static ?int $sort = 7;

    protected static bool $isLazy = true;

    public static function canView(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $now = Carbon::now();

        $totalSales = PurchaseTransaction::paid()->sum('amount');
        $totalPurchases = PurchaseTransaction::paid()->count();

        $monthlySales = PurchaseTransaction::paid()
            ->whereYear('paid_at', $now->year)
            ->whereMonth('paid_at', $now->month)
            ->sum('amount');

        $monthlyPurchases = PurchaseTransaction::paid()
            ->whereYear('paid_at', $now->year)
            ->whereMonth('paid_at', $now->month)
            ->count();

        $totalRefunds = PurchaseTransaction::where('status', 'refunded')->sum('amount');
        $totalRefundCount = PurchaseTransaction::where('status', 'refunded')->count();

        $prevMonthSales = PurchaseTransaction::paid()
            ->whereYear('paid_at', $now->copy()->subMonth()->year)
            ->whereMonth('paid_at', $now->copy()->subMonth()->month)
            ->sum('amount');

        $salesChange = $prevMonthSales > 0
            ? round((($monthlySales - $prevMonthSales) / $prevMonthSales) * 100, 1)
            : ($monthlySales > 0 ? 100 : 0);

        $salesTrend = $salesChange >= 0 ? 'up' : 'down';
        $salesDescription = abs($salesChange).'% vs last month';

        return [
            Stat::make('Total Sales Revenue', '$'.number_format((float) $totalSales, 2))
                ->color('success')
                ->icon('heroicon-o-banknotes')
                ->description('All-time paid transactions'),

            Stat::make('Total Purchases', (string) $totalPurchases)
                ->color('primary')
                ->icon('heroicon-o-shopping-cart')
                ->description('All-time completed purchases'),

            Stat::make('Sales This Month', '$'.number_format((float) $monthlySales, 2))
                ->color($salesTrend === 'up' ? 'success' : 'danger')
                ->icon('heroicon-o-arrow-trending-up')
                ->description($salesDescription)
                ->descriptionIcon($salesTrend === 'up' ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->descriptionColor($salesTrend === 'up' ? 'success' : 'danger'),

            Stat::make('Purchases This Month', (string) $monthlyPurchases)
                ->color('warning')
                ->icon('heroicon-o-calendar')
                ->description($now->format('F Y')),

            Stat::make('Total Refunds', '$'.number_format((float) $totalRefunds, 2))
                ->color('danger')
                ->icon('heroicon-o-arrow-uturn-left')
                ->description($totalRefundCount.' refunded transactions'),
        ];
    }
}
