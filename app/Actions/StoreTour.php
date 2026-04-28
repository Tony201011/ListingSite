<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\ProviderProfile;

class StoreTour
{
    public function execute(?ProviderProfile $profile, array $validated): ActionResult
    {
        if (! $profile) {
            return ActionResult::authorizationFailure('No active profile selected.', 401);
        }

        $tour = $profile->tours()->create($validated);

        return ActionResult::success([
            'tour' => $tour,
        ], 'Tour created successfully.', 201);
    }
}
