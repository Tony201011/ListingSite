<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class ProviderRegistrationsChart extends ChartWidget
{
    protected static ?string $heading = 'Provider Registrations';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    protected function getData(): array
    {
        $start = Carbon::now()->subMonths(11)->startOfMonth();

        $rows = User::query()
            ->where('role', User::ROLE_PROVIDER)
            ->where('created_at', '>=', $start)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $data = [];
        $labels = [];
        $now = Carbon::now();

        for ($i = 11; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $key = $month->format('Y-m');
            $labels[] = $month->format('M Y');
            $data[] = $rows->get($key, 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'New Providers',
                    'data' => $data,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
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
