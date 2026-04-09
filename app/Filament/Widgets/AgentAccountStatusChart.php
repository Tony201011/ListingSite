<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class AgentAccountStatusChart extends ChartWidget
{
    protected ?string $heading = 'Provider Account Status';

    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'agent';
    }

    protected function getData(): array
    {
        $agentId = Filament::auth()->id();

        $stats = User::query()
            ->where('role', User::ROLE_PROVIDER)
            ->whereHas('providerProfile', function ($query) use ($agentId): void {
                $query->where('agent_id', $agentId);
            })
            ->selectRaw("
                SUM(CASE WHEN is_blocked = 0 AND email_verified_at IS NOT NULL THEN 1 ELSE 0 END) as active_verified,
                SUM(CASE WHEN is_blocked = 0 AND email_verified_at IS NULL THEN 1 ELSE 0 END) as active_unverified,
                SUM(CASE WHEN is_blocked = 1 THEN 1 ELSE 0 END) as blocked
            ")
            ->first();

        return [
            'datasets' => [
                [
                    'label' => 'My Providers',
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
