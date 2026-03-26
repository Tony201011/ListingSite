<?php

namespace App\Actions;

use App\Models\RateGroup;
use App\Models\User;

class UpdateRateGroup
{
    public function execute(?User $user, RateGroup $group, array $validated): RateGroup
    {
        if (! $user || $group->user_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $group->update($validated);

        return $group->fresh();
    }
}
