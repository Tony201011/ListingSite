<?php

namespace App\Actions;

use App\Models\RateGroup;
use App\Models\User;

class UpdateRateGroup
{
    public function execute(User $user, RateGroup $group, array $validated): RateGroup
    {
        $group->update($validated);

        return $group->fresh();
    }
}
