<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\ProviderProfile;
use App\Models\RateGroup;

class UpdateRateGroup
{
    public function execute(ProviderProfile $profile, RateGroup $group, array $validated): ActionResult
    {
        if ((int) $group->provider_profile_id !== (int) $profile->id) {
            return ActionResult::authorizationFailure('You can only modify your own rate groups.');
        }

        $group->update($validated);

        return ActionResult::success([
            'group' => $group->fresh(),
        ], 'Rate group updated successfully.');
    }
}
