<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ProviderListing;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DummyProviderListingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoryIds = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('id')
            ->values()
            ->all();

        $providers = User::query()
            ->where('email', 'regexp', '^provider[0-9]+@example\\.com$')
            ->orderBy('id')
            ->take(10)
            ->get();

        foreach ($providers as $index => $provider) {
            $listingNumber = $index + 1;
            $categoryId = count($categoryIds) > 0 ? $categoryIds[$index % count($categoryIds)] : null;
            $thumbnailPath = "provider-listings/dummy-{$listingNumber}.svg";

            Storage::disk('public')->put($thumbnailPath, $this->buildDummyThumbnailSvg($listingNumber));

            ProviderListing::query()->updateOrCreate(
                [
                    'user_id' => $provider->id,
                    'title' => "Dummy Listing {$listingNumber}",
                ],
                [
                    'age' => rand(21, 34),
                    'category_id' => $categoryId,
                    'website_type' => $listingNumber % 2 === 0 ? 'adult' : 'porn',
                    'audience_score' => rand(60, 98),
                    'thumbnail' => $thumbnailPath,
                    'is_live' => $listingNumber % 3 === 0,
                    'is_vip' => $listingNumber <= 3,
                    'is_active' => true,
                ],
            );
        }
    }

    private function buildDummyThumbnailSvg(int $index): string
    {
        $colors = [
            ['#1F2937', '#4B5563'],
            ['#7C2D12', '#C2410C'],
            ['#1E3A8A', '#2563EB'],
            ['#14532D', '#16A34A'],
            ['#581C87', '#9333EA'],
        ];

        [$start, $end] = $colors[$index % count($colors)];

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="512" height="512" viewBox="0 0 512 512">
    <defs>
        <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="{$start}" />
            <stop offset="100%" stop-color="{$end}" />
        </linearGradient>
    </defs>
    <rect width="512" height="512" fill="url(#bg)" rx="24" />
    <text x="50%" y="48%" text-anchor="middle" font-size="56" fill="#FFFFFF" font-family="Arial, sans-serif" font-weight="700">Dummy</text>
    <text x="50%" y="60%" text-anchor="middle" font-size="44" fill="#E5E7EB" font-family="Arial, sans-serif">#{$index}</text>
</svg>
SVG;
    }
}