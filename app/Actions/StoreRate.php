<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\ProviderProfile;

class StoreRate
{
    public function execute(?ProviderProfile $profile, array $validated): ActionResult
    {
        if (! $profile) {
            return ActionResult::authorizationFailure('No active profile selected.', 401);
        }

        $rate = $profile->rates()->create(array_merge($validated, [
            'user_id' => $profile->user_id,
        ]));

        return ActionResult::success([
            'rate' => $rate,
        ], 'Rate created successfully.', 201);
    }
}
