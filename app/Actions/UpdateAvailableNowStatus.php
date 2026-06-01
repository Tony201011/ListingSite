<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\AvailableNow;
use App\Models\ProviderProfile;

class UpdateAvailableNowStatus
{
    public function execute(ProviderProfile $profile, string $status): ActionResult
    {
        $available = $this->getOrCreateAvailableNow($profile);

        if ($status === 'online') {
            return $this->goOnline($available, $profile);
        }

        return $this->goOffline($available);
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

        $available->resetDailyUsageIfNeeded();

        return $available;
    }

    protected function goOnline(AvailableNow $available, ProviderProfile $profile): ActionResult
    {
        if ($this->isFreeListingExpiredWithNegativeBalance($profile)) {
            return new ActionResult(
                false,
                422,
                'Your 21-day period has expired and your account balance is negative. Please clear your balance to go online or become available now.',
                [
                    'status' => 'offline',
                    'expires_at' => null,
                ],
                'domain'
            );
        }

        if ($available->isCurrentlyAvailable()) {
            return ActionResult::success([
                'status' => 'online',
                'expires_at' => optional($available->available_expires_at)?->toIso8601String(),
                'online_started_at' => optional($available->available_started_at)?->toIso8601String(),
            ], 'Available Now is already enabled.');
        }

        $available->status = 'online';
        $available->available_started_at = $available->available_started_at ?? now();
        $available->available_expires_at = null;
        $available->save();

        return ActionResult::success([
            'status' => 'online',
            'expires_at' => optional($available->available_expires_at)?->toIso8601String(),
            'online_started_at' => optional($available->available_started_at)?->toIso8601String(),
        ], 'Available Now enabled.');
    }

    protected function goOffline(AvailableNow $available): ActionResult
    {
        $available->status = 'offline';
        $available->available_started_at = null;
        $available->available_expires_at = null;
        $available->save();

        return ActionResult::success([
            'status' => 'offline',
            'expires_at' => null,
            'online_started_at' => null,
        ], 'Available Now disabled.');
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
