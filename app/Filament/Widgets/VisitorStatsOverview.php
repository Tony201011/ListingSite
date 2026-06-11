<?php

namespace App\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class VisitorStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Visitor Metrics';

    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $totalVisitors = DB::table('sessions')->count();
        $uniqueUsers = DB::table('login_logs')
            ->count(DB::raw('DISTINCT user_id'));
        $monthlyVisits = DB::table('sessions')
            ->whereBetween('last_activity', [
                Carbon::now()->startOfMonth()->timestamp,
                Carbon::now()->endOfMonth()->timestamp,
            ])
            ->count();
        $uniqueToday = DB::table('sessions')
            ->whereNotNull('user_id')
            ->whereBetween('last_activity', [
                Carbon::today()->startOfDay()->timestamp,
                Carbon::today()->endOfDay()->timestamp,
            ])
            ->count(DB::raw('DISTINCT user_id'));

        return [
            Stat::make('Total Visitors', (string) $totalVisitors)
                ->color('primary')
                ->icon('heroicon-o-eye'),
            Stat::make('Unique Users', (string) $uniqueUsers)
                ->color('success')
                ->icon('heroicon-o-user-group'),
            Stat::make('Visits This Month', (string) $monthlyVisits)
                ->color('warning')
                ->icon('heroicon-o-calendar'),
            Stat::make('Unique Users Today', (string) $uniqueToday)
                ->color('secondary')
                ->icon('heroicon-o-user-circle')
                ->description('Distinct authenticated users with sessions recorded today.'),
        ];
    }

    protected function getDescription(): ?string
    {
        return 'Overall visitor and authenticated user totals from site sessions.';
    }
}
