<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\RateGroup;
use App\Models\User;

class UpdateRateGroup
{
    public function execute(User $user, RateGroup $group, array $validated): ActionResult
    {
        if ((int) $group->user_id !== (int) $user->id) {
            return ActionResult::authorizationFailure('You can only modify your own rate groups.');
        }

        $group->update($validated);

        return ActionResult::success([
            'group' => $group->fresh(),
        ], 'Rate group updated successfully.');
    }
}
