<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\ProviderProfile;
use App\Models\Rate;

class DeleteRate
{
    public function execute(ProviderProfile $profile, Rate $rate): ActionResult
    {
        if ((int) $rate->provider_profile_id !== (int) $profile->id) {
            return ActionResult::authorizationFailure('You can only modify your own rates.');
        }

        $rate->delete();

        return ActionResult::success([], 'Rate deleted successfully.');
    }
}
