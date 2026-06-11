<?php

namespace App\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UniqueUsersChart extends ChartWidget
{
    protected ?string $heading = 'Unique Users';

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 1;

    protected static bool $isLazy = true;

    protected ?string $maxHeight = '360px';

    public static function canView(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    protected function getFilters(): ?array
    {
        $currentYear = (int) Carbon::now()->year;
        $years = ['all' => 'All Time'];

        for ($year = $currentYear; $year >= $currentYear - 4; $year--) {
            $years[(string) $year] = (string) $year;
        }

        return $years;
    }

    protected function getData(): array
    {
        $labels = [];
        $counts = [];

        if ($this->filter && $this->filter !== 'all') {
            $year = (int) $this->filter;
            $labels = collect(range(1, 12))
                ->map(fn (int $month) => Carbon::createFromDate($year, $month, 1)->format('M'))
                ->all();

            $rawCounts = DB::table('login_logs')
                ->selectRaw($this->monthExpr('created_at') . ' as month, COUNT(DISTINCT user_id) as count')
                ->whereYear('created_at', $year)
                ->groupBy('month')
                ->pluck('count', 'month');

            $counts = collect(range(1, 12))
                ->map(fn (int $month) => (int) $rawCounts->get($month, 0))
                ->all();
        } else {
            $rawCounts = DB::table('login_logs')
                ->selectRaw($this->yearExpr('created_at') . ' as year, COUNT(DISTINCT user_id) as count')
                ->groupBy('year')
                ->orderBy('year')
                ->pluck('count', 'year');

            $counts = $rawCounts->map(fn (int $count, string $year) => $count)->values()->all();
            $labels = $rawCounts->keys()->map(fn (string $year) => $year)->all();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Unique Users',
                    'data' => $counts,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.7)',
                    'borderColor' => 'rgba(5, 150, 105, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    private function monthExpr(string $column): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%m', {$column}) AS INTEGER)"
            : "MONTH({$column})";
    }

    private function yearExpr(string $column): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%Y', {$column}) AS INTEGER)"
            : "YEAR({$column})";
    }
}
