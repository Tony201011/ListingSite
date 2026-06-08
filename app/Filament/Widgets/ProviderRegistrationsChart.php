<?php

namespace App\Filament\Widgets;

use App\Models\ProviderProfile;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class ProviderRegistrationsChart extends ChartWidget
{
    protected ?string $heading = 'Registration Trends';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 2;

    protected ?string $maxHeight = '420px';

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

        $accountRows = User::query()
            ->withoutTrashed()
            ->whereYear('created_at', $selectedYear)
            ->pluck('created_at')
            ->groupBy(fn ($date) => Carbon::parse($date)->format('Y-m'))
            ->map(fn ($group) => $group->count());

        $providerRows = ProviderProfile::query()
            ->withoutTrashed()
            ->whereYear('created_at', $selectedYear)
            ->pluck('created_at')
            ->groupBy(fn ($date) => Carbon::parse($date)->format('Y-m'))
            ->map(fn ($group) => $group->count());

        $accountData = [];
        $providerData = [];
        $labels = [];

        for ($m = 1; $m <= 12; $m++) {
            $month = Carbon::createFromDate($selectedYear, $m, 1);
            $key = $month->format('Y-m');
            $labels[] = $month->format('M Y');
            $accountData[] = (int) $accountRows->get($key, 0);
            $providerData[] = (int) $providerRows->get($key, 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Account Registrations',
                    'data' => $accountData,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.15)',
                    'borderColor' => 'rgba(16, 185, 129, 1)',
                    'borderWidth' => 2,
                    'fill' => false,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Provider Registrations',
                    'data' => $providerData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.15)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
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
