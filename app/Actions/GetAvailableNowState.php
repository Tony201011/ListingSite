<?php

namespace App\Actions;

use App\Models\AvailableNow;
use App\Models\ProviderProfile;
use App\Models\SiteSetting;

class GetAvailableNowState
{
    public function execute(?ProviderProfile $profile): array
    {
        $settings = SiteSetting::getStatusSettings();
        $maxUses = $settings['available_now_max_uses'];

        $status = false;
        $remainingUses = $maxUses;
        $expiresAt = null;
        $blockedBalance = false;

        if (! $profile) {
            return compact('status', 'remainingUses', 'expiresAt', 'blockedBalance');
        }

        $available = $this->getOrCreateAvailableNow($profile->id);

        $this->syncExpiredStatus($available);

        if ($available->isDirty()) {
            $available->save();
        }

        $status = $available->isCurrentlyAvailable();
        $remainingUses = max(0, $maxUses - $available->usage_count);
        $expiresAt = optional($available->available_expires_at)?->toIso8601String();
        $blockedBalance = $this->isFreeListingExpiredWithNegativeBalance($profile);

        return compact('status', 'remainingUses', 'expiresAt', 'blockedBalance');
    }

    protected function getOrCreateAvailableNow(int $profileId): AvailableNow
    {
        $available = AvailableNow::firstOrCreate(
            ['provider_profile_id' => $profileId],
            [
                'status' => 'offline',
                'usage_date' => today(),
                'usage_count' => 0,
            ]
        );

        $available->resetDailyUsageIfNeeded();

        return $available;
    }

    protected function syncExpiredStatus(AvailableNow $available): void
    {
        if (
            $available->status === 'online' &&
            $available->available_expires_at &&
            now()->greaterThanOrEqualTo($available->available_expires_at)
        ) {
            $available->status = 'offline';
            $available->available_started_at = null;
            $available->available_expires_at = null;
        }
    }

    protected function isFreeListingExpiredWithNegativeBalance(ProviderProfile $profile): bool
    {
        $expiredAt = $profile->free_listing_expires_at;

        if ($expiredAt !== null && $expiredAt->isFuture()) {
            return false;
        }

        $profile->loadMissing('user');

        return $profile->user !== null && $profile->user->credits < 0;
    }
}
