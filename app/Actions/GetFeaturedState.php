<?php

namespace App\Actions;

use App\Models\ProviderProfile;
use App\Models\SiteSetting;

class GetFeaturedState
{
    private const AD_TIER_DURATION_DAYS = 1;

    public function execute(?ProviderProfile $profile): array
    {
        $settings = SiteSetting::getAdTierSettings();

        // Legacy / backward-compat keys (used by existing featured.blade.php and tests)
        $creditCost = $settings['normal_featured_credit_cost'];
        $durationDays = self::AD_TIER_DURATION_DAYS;

        $isFeatured = false;
        $expiresAt = null;
        $homeFeaturedExpiresAt = null;
        $localBannerExpiresAt = null;
        $homeBannerExpiresAt = null;
        $freeListingExpiresAt = null;

        if ($profile) {
            $this->syncExpiredStatus($profile);

            $isFeatured = (bool) $profile->is_featured;
            $expiresAt = $profile->featured_expires_at?->toIso8601String();
            $homeFeaturedExpiresAt = $profile->home_featured_expires_at?->toIso8601String();
            $localBannerExpiresAt = $profile->local_banner_expires_at?->toIso8601String();
            $homeBannerExpiresAt = $profile->home_banner_expires_at?->toIso8601String();
            $freeListingExpiresAt = $profile->free_listing_expires_at?->toIso8601String();
        }

        return compact(
            'isFeatured',
            'expiresAt',
            'creditCost',
            'durationDays',
            'homeFeaturedExpiresAt',
            'localBannerExpiresAt',
            'homeBannerExpiresAt',
            'freeListingExpiresAt',
            'settings',
        );
    }

    protected function syncExpiredStatus(ProviderProfile $profile): void
    {
        $changed = false;

        if (
            $profile->is_featured &&
            $profile->featured_expires_at &&
            now()->greaterThanOrEqualTo($profile->featured_expires_at)
        ) {
            $profile->is_featured = false;
            $profile->featured_expires_at = null;
            $changed = true;
        }

        foreach (['home_featured_expires_at', 'local_banner_expires_at', 'home_banner_expires_at'] as $column) {
            if ($profile->{$column} && now()->greaterThanOrEqualTo($profile->{$column})) {
                $profile->{$column} = null;
                $changed = true;
            }
        }

        if ($changed) {
            $profile->save();
        }
    }
}
