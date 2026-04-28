<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\ProviderProfile;
use App\Models\Tour;

class UpdateTour
{
    public function execute(ProviderProfile $profile, Tour $tour, array $validated): ActionResult
    {
        if ((int) $tour->provider_profile_id !== (int) $profile->id) {
            return ActionResult::authorizationFailure('You can only modify your own tours.');
        }

        $tour->update($validated);

        return ActionResult::success([
            'tour' => $tour->fresh(),
        ], 'Tour updated successfully.');
    }
}
