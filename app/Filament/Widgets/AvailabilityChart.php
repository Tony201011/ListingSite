<?php

namespace App\Filament\Widgets;

use App\Models\AvailableNow;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class AvailabilityChart extends ChartWidget
{
    protected ?string $heading = 'Available Now Users';

    protected static ?int $sort = 11;

    protected int|string|array $columnSpan = 1;

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
                ->map(fn (int $month) => Carbon::createFromDate($year, $month, 1)->format('M Y'))
                ->all();

            $rawCounts = AvailableNow::query()
                ->selectRaw('MONTH(usage_date) as month, COUNT(DISTINCT user_id) as count')
                ->whereYear('usage_date', $year)
                ->groupBy('month')
                ->pluck('count', 'month');

            $counts = collect(range(1, 12))
                ->map(fn (int $month) => (int) $rawCounts->get($month, 0))
                ->all();
        } else {
            $rawCounts = AvailableNow::query()
                ->selectRaw('YEAR(usage_date) as year, COUNT(DISTINCT user_id) as count')
                ->whereNotNull('usage_date')
                ->groupBy('year')
                ->orderBy('year')
                ->pluck('count', 'year');

            $counts = $rawCounts->values()->all();
            $labels = $rawCounts->keys()->all();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Available Now Users',
                    'data' => $counts,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)',
                    'borderColor' => 'rgba(217, 119, 6, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
