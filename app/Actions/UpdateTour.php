<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\Tour;
use App\Models\User;

class UpdateTour
{
    public function execute(User $user, Tour $tour, array $validated): ActionResult
    {
        if ((int) $tour->user_id !== (int) $user->id) {
            return ActionResult::authorizationFailure('You can only modify your own tours.');
        }

        $tour->update($validated);

        return ActionResult::success([
            'tour' => $tour->fresh(),
        ], 'Tour updated successfully.');
    }
}
