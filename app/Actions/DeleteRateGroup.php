<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\ProviderProfile;
use App\Models\RateGroup;
use Illuminate\Support\Facades\DB;

class DeleteRateGroup
{
    public function execute(ProviderProfile $profile, RateGroup $group): ActionResult
    {
        if ((int) $group->provider_profile_id !== (int) $profile->id) {
            return ActionResult::authorizationFailure('You can only modify your own rate groups.');
        }

        DB::transaction(function () use ($group) {
            $group->rates()->update(['group_id' => null]);
            $group->delete();
        });

        return ActionResult::success([], 'Rate group deleted successfully.');
    }
}
