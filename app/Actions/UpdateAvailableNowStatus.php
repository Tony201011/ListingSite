<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\AvailableNow;
use App\Models\ProviderProfile;
use App\Models\SiteSetting;
use App\Services\WalletLedgerService;

class UpdateAvailableNowStatus
{
    public function __construct(
        private WalletLedgerService $walletLedgerService,
    ) {}

    public function execute(ProviderProfile $profile, string $status): ActionResult
    {
        $settings = SiteSetting::getStatusSettings();
        $maxUses = $settings['available_now_max_uses'];
        $durationMinutes = $settings['available_now_duration_minutes'];

        $available = $this->getOrCreateAvailableNow($profile);

        $this->syncExpiredStatus($available);

        if ($status === 'online') {
            return $this->goOnline($available, $maxUses, $durationMinutes, $profile);
        }

        return $this->goOffline($available, $maxUses);
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

    protected function goOnline(AvailableNow $available, int $maxUses, int $durationMinutes, ProviderProfile $profile): ActionResult
    {
        if ($this->isFreeListingExpiredWithNegativeBalance($profile)) {
            return new ActionResult(
                false,
                422,
                'Your 21-day period has expired and this profile balance is negative. Please top up this profile to go online or become available now.',
                [
                    'status' => 'offline',
                    'remaining_uses' => max(0, $maxUses - $available->usage_count),
                    'expires_at' => null,
                ],
                'domain'
            );
        }

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

    protected function isFreeListingExpiredWithNegativeBalance(ProviderProfile $profile): bool
    {
        $expiredAt = $profile->free_listing_expires_at;

        if ($expiredAt !== null && $expiredAt->isFuture()) {
            return false;
        }

        return $this->walletLedgerService->currentBalance($profile) <= 0;
    }
}
