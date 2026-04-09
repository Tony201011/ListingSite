<?php

namespace App\Filament\Widgets;

use App\Models\ProviderProfile;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AgentProfileStatusWidget extends StatsOverviewWidget
{
    protected ?string $heading = 'My Provider Profiles';

    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'agent';
    }

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $stats = ProviderProfile::query()
            ->where('agent_id', Filament::auth()->id())
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN profile_status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN profile_status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN profile_status = 'rejected' THEN 1 ELSE 0 END) as rejected
            ")
            ->first();

        return [
            Stat::make('Total Profiles', (string) ($stats?->total ?? 0))
                ->color('primary')
                ->icon('heroicon-o-user-group'),
            Stat::make('Pending Approval', (string) ($stats?->pending ?? 0))
                ->color('warning')
                ->icon('heroicon-o-clock'),
            Stat::make('Approved Profiles', (string) ($stats?->approved ?? 0))
                ->color('success')
                ->icon('heroicon-o-check-circle'),
            Stat::make('Rejected Profiles', (string) ($stats?->rejected ?? 0))
                ->color('danger')
                ->icon('heroicon-o-x-circle'),
        ];
    }
}
