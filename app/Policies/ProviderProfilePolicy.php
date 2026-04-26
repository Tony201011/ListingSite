<?php

namespace App\Policies;

use App\Models\ProviderProfile;
use App\Models\User;

class ProviderProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === User::ROLE_ADMIN
            || $user->role === User::ROLE_PROVIDER;
    }

    public function create(User $user): bool
    {
        return $user->role === User::ROLE_ADMIN
            || $user->role === User::ROLE_PROVIDER;
    }

    public function view(User $user, ?ProviderProfile $profile = null): bool
    {
        if (! $profile) {
            return $this->viewAny($user);
        }

        return $this->ownsProfile($user, $profile);
    }

    public function update(User $user, ?ProviderProfile $profile = null): bool
    {
        if (! $profile) {
            return $this->create($user);
        }

        return $this->ownsProfile($user, $profile);
    }

    public function viewOwned(User $user, ProviderProfile $profile): bool
    {
        return $this->ownsProfile($user, $profile);
    }

    public function updateOwned(User $user, ProviderProfile $profile): bool
    {
        return $this->ownsProfile($user, $profile);
    }

    public function delete(User $user, ?ProviderProfile $profile = null): bool
    {
        if (! $profile) {
            return false;
        }

        return $this->ownsProfile($user, $profile);
    }

    private function ownsProfile(User $user, ProviderProfile $profile): bool
    {
        if ($user->role === User::ROLE_ADMIN) {
            return true;
        }

        return $profile->user_id === $user->id;
    }
}
