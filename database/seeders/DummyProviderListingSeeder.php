<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ProviderListing;
use App\Models\User;
use Illuminate\Database\Seeder;

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
            ->where('email', 'regexp', '^provider[0-9]+@yopmail\\.com$')
            ->orderBy('id')
            ->take(1000)
            ->get();

        foreach ($providers as $index => $provider) {
            $listingNumber = $index + 1;
            $categoryId = count($categoryIds) > 0 ? $categoryIds[$index % count($categoryIds)] : null;
            $thumbnailUrl = "https://picsum.photos/seed/listing-{$listingNumber}/512/512";

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
                    'thumbnail' => $thumbnailUrl,
                    'is_live' => $listingNumber % 3 === 0,
                    'is_vip' => $listingNumber <= 3,
                    'is_active' => true,
                ],
            );
        }
    }
}
