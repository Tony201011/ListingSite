<?php

namespace App\Actions;

use App\Models\Tour;
use App\Models\User;

class DeleteTour
{
    public function execute(User $user, Tour $tour): void
    {
        $tour->delete();
    }
}
