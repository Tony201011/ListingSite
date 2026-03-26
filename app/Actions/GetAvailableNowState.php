<?php

namespace App\Actions;

use App\Models\AvailableNow;
use App\Models\User;

class GetAvailableNowState
{
    public function execute(?User $user): array
    {
        $status = false;
        $remainingUses = 2;
        $expiresAt = null;

        if (! $user) {
            return compact('status', 'remainingUses', 'expiresAt');
        }

        $available = $this->getOrCreateAvailableNow($user->id);

        $this->syncExpiredStatus($available);
        $available->save();

        $status = $available->isCurrentlyAvailable();
        $remainingUses = max(0, 2 - $available->usage_count);
        $expiresAt = optional($available->available_expires_at)?->toIso8601String();

        return compact('status', 'remainingUses', 'expiresAt');
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
        }
    }
}
