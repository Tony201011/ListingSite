<?php

namespace App\Actions;

use App\Models\RateGroup;
use App\Models\User;

class StoreRateGroup
{
    public function execute(User $user, array $validated): RateGroup
    {
        return RateGroup::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
        ]);
    }
}
