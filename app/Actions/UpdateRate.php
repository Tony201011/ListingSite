<?php

namespace App\Actions;

use App\Models\Rate;
use App\Models\User;

class UpdateRate
{
    public function execute(User $user, Rate $rate, array $validated): Rate
    {
        $rate->update($validated);

        return $rate->fresh();
    }
}
