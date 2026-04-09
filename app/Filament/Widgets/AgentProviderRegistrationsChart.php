<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class AgentProviderRegistrationsChart extends ChartWidget
{
    protected ?string $heading = 'Provider Registrations (My Providers)';

    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'agent';
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
        $agentId = Filament::auth()->id();

        $rows = User::query()
            ->where('role', User::ROLE_PROVIDER)
            ->whereYear('created_at', $selectedYear)
            ->whereHas('providerProfile', function ($query) use ($agentId): void {
                $query->where('agent_id', $agentId);
            })
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
