<?php

namespace App\Filament\Widgets;

use App\Models\ProviderProfile;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class FeaturedListingChart extends ChartWidget
{
    protected ?string $heading = 'Featured Listings Overview';

    protected static ?int $sort = 12;

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '360px';

    public static function canView(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    protected function getData(): array
    {
        $now = Carbon::now();
        $stats = ProviderProfile::query()
            ->withoutTrashed()
            ->whereHas('user', fn ($query) => $query->where('role', User::ROLE_PROVIDER))
            ->selectRaw('
                SUM(CASE WHEN is_featured = 1 AND featured_expires_at IS NOT NULL AND featured_expires_at > ? THEN 1 ELSE 0 END) as normal_featured,
                SUM(CASE WHEN home_featured_expires_at IS NOT NULL AND home_featured_expires_at > ? THEN 1 ELSE 0 END) as home_featured,
                SUM(CASE WHEN local_banner_expires_at IS NOT NULL AND local_banner_expires_at > ? THEN 1 ELSE 0 END) as local_banner,
                SUM(CASE WHEN home_banner_expires_at IS NOT NULL AND home_banner_expires_at > ? THEN 1 ELSE 0 END) as home_banner
            ', [$now, $now, $now, $now])
            ->first();

        return [
            'datasets' => [
                [
                    'label' => 'Active Placements',
                    'data' => [
                        (int) ($stats?->normal_featured ?? 0),
                        (int) ($stats?->home_featured ?? 0),
                        (int) ($stats?->local_banner ?? 0),
                        (int) ($stats?->home_banner ?? 0),
                    ],
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(168, 85, 247, 0.7)',
                        'rgba(245, 158, 11, 0.7)',
                        'rgba(239, 68, 68, 0.7)',
                    ],
                    'borderColor' => [
                        'rgba(37, 99, 235, 1)',
                        'rgba(147, 51, 234, 1)',
                        'rgba(217, 119, 6, 1)',
                        'rgba(220, 38, 38, 1)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => [
                'Featured Listing',
                'Home Page Featured',
                'Local Banner',
                'Home Page Banner',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
