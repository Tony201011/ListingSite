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
        $baseQuery = ProviderProfile::query()
            ->withoutTrashed()
            ->whereHas('user', fn ($query) => $query->where('role', User::ROLE_PROVIDER));

        $now = Carbon::now();
        $normalFeaturedCount = (clone $baseQuery)
            ->where('is_featured', true)
            ->where('featured_expires_at', '>', $now)
            ->count();
        $homeFeaturedCount = (clone $baseQuery)
            ->where('home_featured_expires_at', '>', $now)
            ->count();
        $localBannerCount = (clone $baseQuery)
            ->where('local_banner_expires_at', '>', $now)
            ->count();
        $homeBannerCount = (clone $baseQuery)
            ->where('home_banner_expires_at', '>', $now)
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Active Placements',
                    'data' => [
                        $normalFeaturedCount,
                        $homeFeaturedCount,
                        $localBannerCount,
                        $homeBannerCount,
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
