<?php

namespace App\Actions;

use App\Models\AvailableNow;
use App\Models\User;

class UpdateAvailableNowStatus
{
    public function execute(User $user, string $status): array
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

    protected function goOnline(AvailableNow $available): array
    {
        if ($available->isCurrentlyAvailable()) {
            return [
                'code' => 200,
                'data' => [
                    'success' => true,
                    'status' => 'online',
                    'message' => 'You are already available now.',
                    'remaining_uses' => max(0, 2 - $available->usage_count),
                    'expires_at' => optional($available->available_expires_at)?->toIso8601String(),
                ],
            ];
        }

        if ($available->usage_count >= 2) {
            return [
                'code' => 422,
                'data' => [
                    'success' => false,
                    'status' => 'offline',
                    'message' => 'You have already used Available Now 2 times today.',
                    'remaining_uses' => 0,
                    'expires_at' => null,
                ],
            ];
        }

        $available->status = 'online';
        $available->usage_date = today();
        $available->usage_count += 1;
        $available->available_started_at = now();
        $available->available_expires_at = now()->addHours(2);
        $available->save();

        return [
            'code' => 200,
            'data' => [
                'success' => true,
                'status' => 'online',
                'message' => 'You are now available for enquiries for 2 hours.',
                'remaining_uses' => max(0, 2 - $available->usage_count),
                'expires_at' => optional($available->available_expires_at)?->toIso8601String(),
            ],
        ];
    }

    protected function goOffline(AvailableNow $available): array
    {
        $available->status = 'offline';
        $available->available_started_at = null;
        $available->available_expires_at = null;
        $available->save();

        return [
            'code' => 200,
            'data' => [
                'success' => true,
                'status' => 'offline',
                'message' => 'You are now unavailable.',
                'remaining_uses' => max(0, 2 - $available->usage_count),
                'expires_at' => null,
            ],
        ];
    }
}
