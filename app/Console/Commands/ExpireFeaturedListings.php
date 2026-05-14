<?php

namespace App\Console\Commands;

use App\Models\ProviderProfile;
use Illuminate\Console\Command;

class ExpireFeaturedListings extends Command
{
    protected $signature = 'featured:expire';

    protected $description = 'Expire featured listings whose featured_expires_at has passed';

    public function handle(): int
    {
        $count = ProviderProfile::query()
            ->where('is_featured', true)
            ->whereNotNull('featured_expires_at')
            ->where('featured_expires_at', '<=', now())
            ->update([
                'is_featured' => false,
                'featured_expires_at' => null,
            ]);

        $this->info("Expired {$count} featured listing(s).");

        return self::SUCCESS;
    }
}
