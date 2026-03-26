<?php

namespace App\Actions;

use App\Models\Rate;
use App\Models\User;

class StoreRate
{
    public function execute(?User $user, array $validated): Rate
    {
        if (! $user) {
            abort(403);
        }

        return $user->rates()->create($validated);
    }
}
