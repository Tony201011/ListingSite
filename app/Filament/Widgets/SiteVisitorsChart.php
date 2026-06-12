<?php

namespace App\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SiteVisitorsChart extends ChartWidget
{
    protected ?string $heading = 'Site Visitors';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;

    protected static bool $isLazy = true;

    protected ?string $maxHeight = '360px';

    protected ?string $pollingInterval = '5m';

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

            $rawCounts = DB::table('sessions')
                ->selectRaw($this->monthFromUnixExpr() . ' as month, COUNT(*) as count')
                ->whereBetween('last_activity', [
                    Carbon::createFromDate($year, 1, 1)->startOfYear()->timestamp,
                    Carbon::createFromDate($year, 12, 31)->endOfYear()->timestamp,
                ])
                ->groupBy('month')
                ->pluck('count', 'month');

            $counts = collect(range(1, 12))
                ->map(fn (int $month) => (int) $rawCounts->get($month, 0))
                ->all();
        } else {
            $labels = [];
            $rawCounts = DB::table('sessions')
                ->selectRaw($this->yearFromUnixExpr() . ' as year, COUNT(*) as count')
                ->groupBy('year')
                ->orderBy('year')
                ->pluck('count', 'year');

            $counts = $rawCounts->map(fn (int $count, string $year) => $count)->values()->all();
            $labels = $rawCounts->keys()->map(fn (string $year) => $year)->all();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Visitors',
                    'data' => $counts,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.7)',
                    'borderColor' => 'rgba(37, 99, 235, 1)',
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

    private function monthFromUnixExpr(): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%m', datetime(last_activity, 'unixepoch')) AS INTEGER)"
            : 'MONTH(FROM_UNIXTIME(last_activity))';
    }

    private function yearFromUnixExpr(): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%Y', datetime(last_activity, 'unixepoch')) AS INTEGER)"
            : 'YEAR(FROM_UNIXTIME(last_activity))';
    }
}
