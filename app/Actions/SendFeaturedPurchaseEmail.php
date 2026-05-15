<?php

namespace App\Actions;

use App\Jobs\SendFeaturedPurchaseEmailJob;
use App\Models\ProviderProfile;
use App\Models\User;
use Carbon\CarbonInterface;

class SendFeaturedPurchaseEmail
{
    public function execute(
        User $user,
        ProviderProfile $profile,
        string $tierLabel,
        int $creditCost,
        int $durationDays,
        ?CarbonInterface $expiresAt,
        bool $isExtension = false,
        ?CarbonInterface $previousExpiry = null,
    ): void {
        if (! filled($user->email)) {
            return;
        }

        SendFeaturedPurchaseEmailJob::dispatchSync(
            $user->email,
            $user->name,
            $profile->name ?: $user->name,
            $tierLabel,
            $creditCost,
            $durationDays,
            $expiresAt?->toIso8601String(),
            $isExtension,
            $previousExpiry?->toIso8601String(),
        );
    }
}
