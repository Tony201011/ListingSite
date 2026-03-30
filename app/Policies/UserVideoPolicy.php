<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserVideo;

class UserVideoPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, UserVideo $video): bool
    {
        return $video->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, UserVideo $video): bool
    {
        return $video->user_id === $user->id;
    }

    public function delete(User $user, UserVideo $video): bool
    {
        return $video->user_id === $user->id;
    }
}
