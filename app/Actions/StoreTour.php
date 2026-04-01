<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\Tour;
use App\Models\User;

class StoreTour
{
    public function execute(?User $user, array $validated): ActionResult
    {
        if (! $user) {
            return ActionResult::authorizationFailure('Unauthenticated.', 401);
        }

        if (! $user->providerProfile()->exists()) {
            return ActionResult::authorizationFailure('Provider profile is required to manage tours.');
        }

        $tour = $user->tours()->create($validated);

        return ActionResult::success([
            'tour' => $tour,
        ], 'Tour created successfully.', 201);
    }
}
