<?php

namespace App\Actions;

use App\Models\Tour;
use App\Models\User;

class UpdateTour
{
    public function execute(User $user, Tour $tour, array $validated): Tour
    {
        $tour->update($validated);

        return $tour->fresh();
    }
}
