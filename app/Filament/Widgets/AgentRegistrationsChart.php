<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class AgentRegistrationsChart extends ChartWidget
{
    protected ?string $heading = 'Agent Registrations';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

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

        $rows = User::query()
            ->where('role', User::ROLE_AGENT)
            ->whereYear('created_at', $selectedYear)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $data = [];
        $labels = [];

        for ($m = 1; $m <= 12; $m++) {
            $month = Carbon::createFromDate($selectedYear, $m, 1);
            $key = $month->format('Y-m');
            $labels[] = $month->format('M Y');
            $data[] = $rows->get($key, 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'New Agents',
                    'data' => $data,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)',
                    'borderColor' => 'rgba(245, 158, 11, 1)',
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
