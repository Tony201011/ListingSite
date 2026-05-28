<?php

namespace App\Filament\Widgets;

use App\Models\ProviderProfile;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class AccountStatusChart extends ChartWidget
{
    protected ?string $heading = 'Account Status Overview';

    protected static ?int $sort = 5;

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
        $query = ProviderProfile::query()
            ->withoutTrashed()
            ->whereHas('user', fn ($query) => $query->where('role', User::ROLE_PROVIDER));

        if ($this->filter && $this->filter !== 'all') {
            $query->whereYear('provider_profiles.created_at', (int) $this->filter);
        }

        $stats = $query
            ->selectRaw('
                SUM(CASE WHEN provider_profiles.is_blocked = 0 AND users.email_verified_at IS NOT NULL THEN 1 ELSE 0 END) as active_verified,
                SUM(CASE WHEN provider_profiles.is_blocked = 0 AND users.email_verified_at IS NULL THEN 1 ELSE 0 END) as active_unverified,
                SUM(CASE WHEN provider_profiles.is_blocked = 1 THEN 1 ELSE 0 END) as blocked
            ')
            ->join('users', 'users.id', '=', 'provider_profiles.user_id')
            ->first();

        return [
            'datasets' => [
                [
                    'label' => 'Providers',
                    'data' => [
                        (int) ($stats?->active_verified ?? 0),
                        (int) ($stats?->active_unverified ?? 0),
                        (int) ($stats?->blocked ?? 0),
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
