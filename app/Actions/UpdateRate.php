<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\ProviderProfile;
use App\Models\Rate;

class UpdateRate
{
    public function execute(ProviderProfile $profile, Rate $rate, array $validated): ActionResult
    {
        if ((int) $rate->provider_profile_id !== (int) $profile->id) {
            return ActionResult::authorizationFailure('You can only modify your own rates.');
        }

        $rate->update($validated);

        return ActionResult::success([
            'rate' => $rate->fresh(),
        ], 'Rate updated successfully.');
    }
}
