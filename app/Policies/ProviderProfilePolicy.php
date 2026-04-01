<?php

namespace App\Policies;

use App\Models\ProviderProfile;
use App\Models\User;

class ProviderProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === User::ROLE_ADMIN
            || ($user->role === User::ROLE_AGENT && ! $user->is_blocked)
            || $user->providerProfile()->exists();
    }

    public function create(User $user): bool
    {
        return $user->role === User::ROLE_ADMIN
            || ($user->role === User::ROLE_AGENT && ! $user->is_blocked)
            || $user->role === User::ROLE_PROVIDER;
    }

    public function view(User $user, ProviderProfile $profile): bool
    {
        return $this->ownsProfile($user, $profile);
    }

    public function update(User $user, ProviderProfile $profile): bool
    {
        return $this->ownsProfile($user, $profile);
    }

    public function delete(User $user, ProviderProfile $profile): bool
    {
        return $this->ownsProfile($user, $profile);
    }

    private function ownsProfile(User $user, ProviderProfile $profile): bool
    {
        if ($user->role === User::ROLE_ADMIN) {
            return true;
        }

        if ($user->role === User::ROLE_AGENT && ! $user->is_blocked) {
            return $profile->agent_id === $user->id;
        }

        return $profile->user_id === $user->id;
    }
}
