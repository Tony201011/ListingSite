<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\Rate;
use App\Models\User;

class UpdateRate
{
    public function execute(User $user, Rate $rate, array $validated): ActionResult
    {
        if ((int) $rate->user_id !== (int) $user->id) {
            return ActionResult::authorizationFailure('You can only modify your own rates.');
        }

        $rate->update($validated);

        return ActionResult::success([
            'rate' => $rate->fresh(),
        ], 'Rate updated successfully.');
    }
}
