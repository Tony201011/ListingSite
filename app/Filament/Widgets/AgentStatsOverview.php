<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AgentStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Agent Insights';

    protected static ?int $sort = 5;

    public static function canView(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $agents = User::query()->where('role', User::ROLE_AGENT);

        $total = (clone $agents)->count();
        $active = (clone $agents)->where('is_blocked', false)->count();
        $blocked = (clone $agents)->where('is_blocked', true)->count();
        $verified = (clone $agents)->whereNotNull('email_verified_at')->count();

        return [
            Stat::make('Total Agents', (string) $total)
                ->color('primary')
                ->icon('heroicon-o-user-group'),
            Stat::make('Active Accounts', (string) $active)
                ->color('success')
                ->icon('heroicon-o-check-circle'),
            Stat::make('Blocked Accounts', (string) $blocked)
                ->color('danger')
                ->icon('heroicon-o-no-symbol'),
            Stat::make('Verified Emails', (string) $verified)
                ->color('warning')
                ->icon('heroicon-o-shield-check'),
        ];
    }

    protected function getDescription(): ?string
    {
        return 'Quick overview of agent accounts';
    }
}