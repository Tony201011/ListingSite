<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\ProviderProfile;
use App\Models\Tour;

class DeleteTour
{
    public function execute(ProviderProfile $profile, Tour $tour): ActionResult
    {
        if ((int) $tour->provider_profile_id !== (int) $profile->id) {
            return ActionResult::authorizationFailure('You can only modify your own tours.');
        }

        $tour->delete();

        return ActionResult::success([], 'Tour deleted successfully.');
    }
}
