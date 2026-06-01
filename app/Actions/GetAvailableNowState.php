<?php

namespace App\Actions;

use App\Models\AvailableNow;
use App\Models\ProviderProfile;

class GetAvailableNowState
{
    public function execute(?ProviderProfile $profile): array
    {
        $status = false;
        $expiresAt = null;
        $blockedBalance = false;
        $startedAt = null;

        if (! $profile) {
            return compact('status', 'expiresAt', 'startedAt', 'blockedBalance');
        }

        $available = $this->getOrCreateAvailableNow($profile);

        $status = $available->isCurrentlyAvailable();
        $expiresAt = optional($available->available_expires_at)?->toIso8601String();
        $startedAt = optional($available->available_started_at)?->toIso8601String();
        $blockedBalance = $this->isFreeListingExpiredWithNegativeBalance($profile);

        return compact('status', 'expiresAt', 'startedAt', 'blockedBalance');
    }

    protected function getOrCreateAvailableNow(ProviderProfile $profile): AvailableNow
    {
        $available = AvailableNow::firstOrCreate(
            ['provider_profile_id' => $profile->id],
            [
                'user_id' => $profile->user_id,
                'status' => 'offline',
                'usage_date' => today(),
                'usage_count' => 0,
            ]
        );

        if ($available->user_id !== $profile->user_id) {
            $available->user_id = $profile->user_id;
            $available->save();
        }

        return $available;
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
