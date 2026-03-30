<?php

namespace App\Policies;

use App\Models\ProviderProfile;
use App\Models\User;

class ProviderProfilePolicy
{
    public function view(User $user): bool
    {
        return $user->providerProfile()->exists();
    }

    public function update(User $user): bool
    {
        return $user->providerProfile()->exists();
    }

    public function create(User $user): bool
    {
        return $user->role === User::ROLE_PROVIDER;
    }

    public function viewOwned(User $user, ProviderProfile $profile): bool
    {
        return $profile->user_id === $user->id;
    }

    public function updateOwned(User $user, ProviderProfile $profile): bool
    {
        return $profile->user_id === $user->id;
    }
}
