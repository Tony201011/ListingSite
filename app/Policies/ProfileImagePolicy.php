<?php

namespace App\Policies;
use App\Models\ProfileImage;
use App\Models\User;


class ProfileImagePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function view(User $user, ProfileImage $profileImage): bool
    {
        return $profileImage->user_id === $user->id;
    }

    public function update(User $user, ProfileImage $profileImage): bool
    {
        return $profileImage->user_id === $user->id;
    }

    public function delete(User $user, ProfileImage $profileImage): bool
    {
        return $profileImage->user_id === $user->id;
    }
}
