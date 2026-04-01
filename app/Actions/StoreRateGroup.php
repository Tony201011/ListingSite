<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\RateGroup;
use App\Models\User;

class StoreRateGroup
{
    public function execute(User $user, array $validated): ActionResult
    {
        if (! $user->providerProfile()->exists()) {
            return ActionResult::authorizationFailure('Provider profile is required to manage rate groups.');
        }

        $group = RateGroup::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
        ]);

        return ActionResult::success([
            'group' => $group,
        ], 'Rate group created successfully.', 201);
    }
}
