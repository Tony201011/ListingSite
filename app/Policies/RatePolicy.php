<?php

namespace App\Policies;

use App\Models\Rate;
use App\Models\User;

class RatePolicy
{
    public function view(User $user, Rate $rate): bool
    {
        return $rate->user_id === $user->id;
    }

    public function update(User $user, Rate $rate): bool
    {
        return $rate->user_id === $user->id;
    }

    public function delete(User $user, Rate $rate): bool
    {
        return $rate->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->providerProfile()->exists();
    }
}
