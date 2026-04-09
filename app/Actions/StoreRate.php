<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\User;

class StoreRate
{
    public function execute(?User $user, array $validated): ActionResult
    {
        if (! $user) {
            return ActionResult::authorizationFailure('Unauthenticated.', 401);
        }

        if (! $user->providerProfile()->exists()) {
            return ActionResult::authorizationFailure('Provider profile is required to manage rates.');
        }

        $rate = $user->rates()->create($validated);

        return ActionResult::success([
            'rate' => $rate,
        ], 'Rate created successfully.', 201);
    }
}
