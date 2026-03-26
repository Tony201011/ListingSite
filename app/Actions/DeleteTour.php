<?php

namespace App\Actions;

use App\Models\Tour;
use App\Models\User;

class DeleteTour
{
    public function execute(?User $user, Tour $tour): void
    {
        if (! $user || $tour->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $tour->delete();
    }
}
