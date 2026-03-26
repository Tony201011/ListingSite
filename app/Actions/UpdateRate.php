<?php

namespace App\Actions;

use App\Models\Rate;
use App\Models\User;

class UpdateRate
{
    public function execute(?User $user, Rate $rate, array $validated): Rate
    {
        if (! $user || $rate->user_id !== $user->id) {
            abort(403);
        }

        $rate->update($validated);

        return $rate->fresh();
    }
}
