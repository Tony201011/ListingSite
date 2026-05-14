<?php

namespace App\Actions;

use App\Models\ProviderProfile;
use App\Models\SiteSetting;

class GetFeaturedState
{
    public function execute(?ProviderProfile $profile): array
    {
        $settings = SiteSetting::getFeaturedSettings();
        $creditCost = $settings['featured_credit_cost'];
        $durationDays = $settings['featured_duration_days'];

        $isFeatured = false;
        $expiresAt = null;

        if ($profile) {
            $this->syncExpiredStatus($profile);

            $isFeatured = (bool) $profile->is_featured;
            $expiresAt = $profile->featured_expires_at?->toIso8601String();
        }

        return compact('isFeatured', 'expiresAt', 'creditCost', 'durationDays');
    }

    protected function syncExpiredStatus(ProviderProfile $profile): void
    {
        if (
            $profile->is_featured &&
            $profile->featured_expires_at &&
            now()->greaterThanOrEqualTo($profile->featured_expires_at)
        ) {
            $profile->is_featured = false;
            $profile->featured_expires_at = null;
            $profile->save();
        }
    }
}
