<?php

namespace App\Console\Commands;

use App\Models\ProviderProfile;
use Illuminate\Console\Command;

class ExpireFeaturedListings extends Command
{
    protected $signature = 'featured:expire';

    protected $description = 'Expire featured listings and ad-tier placements whose expiry has passed';

    public function handle(): int
    {
        // Expire normal featured
        $count = ProviderProfile::query()
            ->where('is_featured', true)
            ->whereNotNull('featured_expires_at')
            ->where('featured_expires_at', '<=', now())
            ->update([
                'is_featured' => false,
                'featured_expires_at' => null,
            ]);

        // Expire home-page featured tier
        ProviderProfile::query()
            ->whereNotNull('home_featured_expires_at')
            ->where('home_featured_expires_at', '<=', now())
            ->update(['home_featured_expires_at' => null]);

        // Expire local (state) banner tier
        ProviderProfile::query()
            ->whereNotNull('local_banner_expires_at')
            ->where('local_banner_expires_at', '<=', now())
            ->update(['local_banner_expires_at' => null]);

        // Expire home-page banner tier
        ProviderProfile::query()
            ->whereNotNull('home_banner_expires_at')
            ->where('home_banner_expires_at', '<=', now())
            ->update(['home_banner_expires_at' => null]);

        $this->info("Expired {$count} normal featured listing(s) and refreshed ad-tier placements.");

        return self::SUCCESS;
    }
}
