<?php

namespace App\Actions;

use App\Models\Tour;
use App\Models\User;

class UpdateTour
{
    public function execute(?User $user, Tour $tour, array $validated): Tour
    {
        if (! $user || $tour->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $tour->update($validated);

        return $tour->fresh();
    }
}
