<?php

namespace App\Actions;

use App\Models\Tour;
use App\Models\User;

class StoreTour
{
    public function execute(?User $user, array $validated): Tour
    {
        if (! $user) {
            abort(403, 'Unauthorized action.');
        }

        return $user->tours()->create($validated);
    }
}
