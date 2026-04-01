<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\AvailableNow;
use App\Models\User;

class UpdateAvailableNowStatus
{
    public function execute(User $user, string $status): ActionResult
    {
        $available = $this->getOrCreateAvailableNow($user->id);

        $this->syncExpiredStatus($available);

        if ($status === 'online') {
            return $this->goOnline($available);
        }

        return $this->goOffline($available);
    }

    protected function getOrCreateAvailableNow(int $userId): AvailableNow
    {
        $available = AvailableNow::firstOrCreate(
            ['user_id' => $userId],
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

    protected function goOnline(AvailableNow $available): ActionResult
    {
        if ($available->isCurrentlyAvailable()) {
            return ActionResult::success([
                'status' => 'online',
                'remaining_uses' => max(0, 2 - $available->usage_count),
                'expires_at' => optional($available->available_expires_at)?->toIso8601String(),
            ], 'You are already available now.');
        }

        if ($available->usage_count >= 2) {
            return new ActionResult(
                false,
                422,
                'You have already used Available Now 2 times today.',
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
        $available->available_expires_at = now()->addHours(2);
        $available->save();

        return ActionResult::success([
            'status' => 'online',
            'remaining_uses' => max(0, 2 - $available->usage_count),
            'expires_at' => optional($available->available_expires_at)?->toIso8601String(),
        ], 'You are now available for enquiries for 2 hours.');
    }

    protected function goOffline(AvailableNow $available): ActionResult
    {
        $available->status = 'offline';
        $available->available_started_at = null;
        $available->available_expires_at = null;
        $available->save();

        return ActionResult::success([
            'status' => 'offline',
            'remaining_uses' => max(0, 2 - $available->usage_count),
            'expires_at' => null,
        ], 'You are now unavailable.');
    }
}
