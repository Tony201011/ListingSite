<?php

namespace App\Filament\Widgets;

use App\Models\PurchaseTransaction;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class PaymentSalesChart extends ChartWidget
{
    protected ?string $heading = 'Sales Revenue';

    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '360px';

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

        $rows = PurchaseTransaction::paid()
            ->whereYear('paid_at', $selectedYear)
            ->get()
            ->groupBy(fn ($t) => Carbon::parse($t->paid_at)->format('n'))
            ->map(fn ($group) => $group->sum('amount'));

        $labels = [];
        $data = [];

        for ($m = 1; $m <= 12; $m++) {
            $labels[] = Carbon::createFromDate($selectedYear, $m, 1)->format('M Y');
            $data[] = (float) $rows->get((string) $m, 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (AUD)',
                    'data' => $data,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'borderColor' => 'rgba(22, 163, 74, 1)',
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
