<?php

namespace App\Actions;

use App\Models\Profile;
use App\Models\User;

class CreateProfileAction
{
    public function execute(User $user, array $attributes): Profile
    {
        return $user->profiles()->create($attributes);
    }
}
