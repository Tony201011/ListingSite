<?php

namespace App\Filament\Widgets;

use App\Models\LoginLog;
use App\Models\ProviderProfile;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class ProviderRegistrationsChart extends ChartWidget
{
    protected ?string $heading = 'Daily Registration & Login Trends';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 2;

    protected static bool $isLazy = true;

    protected ?string $maxHeight = '420px';

    public static function canView(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    protected function getFilters(): ?array
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $months = [];

        for ($offset = 0; $offset < 12; $offset++) {
            $month = $currentMonth->copy()->subMonths($offset);
            $months[$month->format('Y-m')] = $month->format('M Y');
        }

        return $months;
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                        'padding' => 20,
                        'boxWidth' => 8,
                        'boxHeight' => 8,
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'ticks' => [
                        'maxTicksLimit' => 15,
                        'maxRotation' => 45,
                        'minRotation' => 0,
                    ],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }

    protected function getData(): array
    {
        // Always parse as the 1st of the month to avoid day-overflow issues
        // (e.g. createFromFormat on March 31 selecting February would roll to March 3).
        $yearMonth = $this->filter ?? Carbon::now()->format('Y-m');
        $selectedMonth = Carbon::parse($yearMonth . '-01');
        $startDate = $selectedMonth->copy()->startOfMonth();
        $endDate = $selectedMonth->copy()->endOfMonth();
        $daysInMonth = $selectedMonth->daysInMonth;

        $accountRegistrationRows = User::query()
            ->withoutTrashed()
            ->where('role', '!=', User::ROLE_PROVIDER)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as count')
            ->groupBy('day')
            ->pluck('count', 'day');

        $providerRegistrationRows = ProviderProfile::query()
            ->withoutTrashed()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as count')
            ->groupBy('day')
            ->pluck('count', 'day');

        $accountLoginRows = LoginLog::query()
            ->whereHas('user', fn ($query) => $query->where('role', '!=', User::ROLE_PROVIDER))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as count')
            ->groupBy('day')
            ->pluck('count', 'day');

        $providerLoginRows = LoginLog::query()
            ->whereHas('user', fn ($query) => $query->where('role', User::ROLE_PROVIDER))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as count')
            ->groupBy('day')
            ->pluck('count', 'day');

        $accountRegistrationData = [];
        $providerRegistrationData = [];
        $accountLoginData = [];
        $providerLoginData = [];
        $labels = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $startDate->copy()->day($day);
            $key = $date->format('Y-m-d');
            $labels[] = $date->format('d M');
            $accountRegistrationData[] = (int) $accountRegistrationRows->get($key, 0);
            $providerRegistrationData[] = (int) $providerRegistrationRows->get($key, 0);
            $accountLoginData[] = (int) $accountLoginRows->get($key, 0);
            $providerLoginData[] = (int) $providerLoginRows->get($key, 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Account Registrations',
                    'data' => $accountRegistrationData,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.15)',
                    'borderColor' => 'rgba(16, 185, 129, 1)',
                    'borderWidth' => 2,
                    'fill' => false,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Provider Registrations',
                    'data' => $providerRegistrationData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.15)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 2,
                    'fill' => false,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Account Logins',
                    'data' => $accountLoginData,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.15)',
                    'borderColor' => 'rgba(245, 158, 11, 1)',
                    'borderWidth' => 2,
                    'fill' => false,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Provider Logins',
                    'data' => $providerLoginData,
                    'backgroundColor' => 'rgba(168, 85, 247, 0.15)',
                    'borderColor' => 'rgba(168, 85, 247, 1)',
                    'borderWidth' => 2,
                    'fill' => false,
                    'tension' => 0.35,
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
