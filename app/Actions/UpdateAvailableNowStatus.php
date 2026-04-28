<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\AvailableNow;
use App\Models\ProviderProfile;
use App\Models\SiteSetting;

class UpdateAvailableNowStatus
{
    public function execute(ProviderProfile $profile, string $status): ActionResult
    {
        $settings = SiteSetting::getStatusSettings();
        $maxUses = $settings['available_now_max_uses'];
        $durationMinutes = $settings['available_now_duration_minutes'];

        $available = $this->getOrCreateAvailableNow($profile->id);

        $this->syncExpiredStatus($available);

        if ($status === 'online') {
            return $this->goOnline($available, $maxUses, $durationMinutes);
        }

        return $this->goOffline($available, $maxUses);
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
            $available->save();
        }
    }

    protected function goOnline(AvailableNow $available, int $maxUses, int $durationMinutes): ActionResult
    {
        if ($available->isCurrentlyAvailable()) {
            return ActionResult::success([
                'status' => 'online',
                'remaining_uses' => max(0, $maxUses - $available->usage_count),
                'expires_at' => optional($available->available_expires_at)?->toIso8601String(),
            ], 'You are already available now.');
        }

        if ($available->usage_count >= $maxUses) {
            return new ActionResult(
                false,
                422,
                "You have already used Available Now {$maxUses} times today.",
                [
                    'status' => 'offline',
                    'remaining_uses' => 0,
                    'expires_at' => null,
                ],
                'domain'
            );
        }

        $available->status = 'online';
        $available->usage_date = today();
        $available->usage_count += 1;
        $available->available_started_at = now();
        $available->available_expires_at = now()->addMinutes($durationMinutes);
        $available->save();

        return ActionResult::success([
            'status' => 'online',
            'remaining_uses' => max(0, $maxUses - $available->usage_count),
            'expires_at' => optional($available->available_expires_at)?->toIso8601String(),
        ], "You are now available for enquiries for {$durationMinutes} minutes.");
    }

    protected function goOffline(AvailableNow $available, int $maxUses): ActionResult
    {
        $available->status = 'offline';
        $available->available_started_at = null;
        $available->available_expires_at = null;
        $available->save();

        return ActionResult::success([
            'status' => 'offline',
            'remaining_uses' => max(0, $maxUses - $available->usage_count),
            'expires_at' => null,
        ], 'You are now unavailable.');
    }
}
