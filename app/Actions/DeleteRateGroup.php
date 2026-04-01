<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\RateGroup;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteRateGroup
{
    public function execute(User $user, RateGroup $group): ActionResult
    {
        if ((int) $group->user_id !== (int) $user->id) {
            return ActionResult::authorizationFailure('You can only modify your own rate groups.');
        }

        DB::transaction(function () use ($group) {
            $group->rates()->update(['group_id' => null]);
            $group->delete();
        });

        return ActionResult::success([], 'Rate group deleted successfully.');
    }
}
