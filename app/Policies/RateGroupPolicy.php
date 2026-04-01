<?php

namespace App\Policies;

use App\Models\RateGroup;
use App\Models\User;

class RateGroupPolicy
{
    public function create(User $user): bool
    {
        return $user->providerProfile()->exists();
    }

    public function view(User $user, RateGroup $group): bool
    {
        return $group->user_id === $user->id;
    }

    public function update(User $user, RateGroup $group): bool
    {
        return $group->user_id === $user->id;
    }

    public function delete(User $user, RateGroup $group): bool
    {
        return $group->user_id === $user->id;
    }
}
