<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\Tour;
use App\Models\User;

class DeleteTour
{
    public function execute(User $user, Tour $tour): ActionResult
    {
        if ((int) $tour->user_id !== (int) $user->id) {
            return ActionResult::authorizationFailure('You can only modify your own tours.');
        }

        $tour->delete();

        return ActionResult::success([], 'Tour deleted successfully.');
    }
}
