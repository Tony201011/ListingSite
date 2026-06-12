<?php

namespace App\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentPurchasesChart extends ChartWidget
{
    protected ?string $heading = 'Number of Purchases';

    protected static ?int $sort = 9;

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
        $years = [];

        for ($year = $currentYear; $year >= $currentYear - 4; $year--) {
            $years[(string) $year] = (string) $year;
        }

        return $years;
    }

    protected function getData(): array
    {
        $selectedYear = (int) ($this->filter ?? Carbon::now()->year);

        $rows = DB::table('purchase_transactions')
            ->selectRaw('MONTH(paid_at) as month, COUNT(*) as total')
            ->where('status', 'paid')
            ->whereYear('paid_at', $selectedYear)
            ->groupBy('month')
            ->pluck('total', 'month');

        $labels = [];
        $data = [];

        for ($m = 1; $m <= 12; $m++) {
            $labels[] = Carbon::createFromDate($selectedYear, $m, 1)->format('M Y');
            $data[] = (int) $rows->get((string) $m, 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Purchases',
                    'data' => $data,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.6)',
                    'borderColor' => 'rgba(37, 99, 235, 1)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
