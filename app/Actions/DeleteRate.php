<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\Rate;
use App\Models\User;

class DeleteRate
{
    public function execute(User $user, Rate $rate): ActionResult
    {
        if ((int) $rate->user_id !== (int) $user->id) {
            return ActionResult::authorizationFailure('You can only modify your own rates.');
        }

        $rate->delete();

        return ActionResult::success([], 'Rate deleted successfully.');
    }
}
