<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class AccountStatusChart extends ChartWidget
{
    protected ?string $heading = 'Account Status Overview';

    protected static ?int $sort = 4;

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
        $query = User::query()
            ->where('role', User::ROLE_PROVIDER);

        if ($this->filter && $this->filter !== 'all') {
            $query->whereYear('created_at', (int) $this->filter);
        }

        $stats = $query
            ->selectRaw('
                role,
                SUM(CASE WHEN is_blocked = 0 AND email_verified_at IS NOT NULL THEN 1 ELSE 0 END) as active_verified,
                SUM(CASE WHEN is_blocked = 0 AND email_verified_at IS NULL THEN 1 ELSE 0 END) as active_unverified,
                SUM(CASE WHEN is_blocked = 1 THEN 1 ELSE 0 END) as blocked
            ')
            ->groupBy('role')
            ->get()
            ->keyBy('role');

        $providers = $stats->get(User::ROLE_PROVIDER);

        return [
            'datasets' => [
                [
                    'label' => 'Providers',
                    'data' => [
                        (int) ($providers?->active_verified ?? 0),
                        (int) ($providers?->active_unverified ?? 0),
                        (int) ($providers?->blocked ?? 0),
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
            'labels' => ['Active & Verified', 'Active & Unverified', 'Blocked'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
