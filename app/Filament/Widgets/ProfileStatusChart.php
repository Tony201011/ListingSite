<?php

namespace App\Filament\Widgets;

use App\Models\ProviderProfile;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class ProfileStatusChart extends ChartWidget
{
    protected ?string $heading = 'Profile Status Overview';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

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
        $query = ProviderProfile::query()->withoutTrashed();

        if ($this->filter && $this->filter !== 'all') {
            $query->whereYear('created_at', (int) $this->filter);
        }

        $counts = $query
            ->selectRaw('profile_status, COUNT(*) as count')
            ->groupBy('profile_status')
            ->pluck('count', 'profile_status');

        return [
            'datasets' => [
                [
                    'label' => 'Profiles',
                    'data' => [
                        (int) $counts->get('approved', 0),
                        (int) $counts->get('pending', 0),
                        (int) $counts->get('rejected', 0),
                    ],
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.7)',
                        'rgba(251, 191, 36, 0.7)',
                        'rgba(239, 68, 68, 0.7)',
                    ],
                    'borderColor' => [
                        'rgba(34, 197, 94, 1)',
                        'rgba(251, 191, 36, 1)',
                        'rgba(239, 68, 68, 1)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => ['Approved', 'Pending', 'Rejected'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
