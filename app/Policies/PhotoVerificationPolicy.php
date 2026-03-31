<?php

namespace App\Policies;

use App\Models\PhotoVerification;
use App\Models\User;

class PhotoVerificationPolicy
{
    public function view(User $user, PhotoVerification $photoVerification): bool
    {
        return $photoVerification->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->providerProfile()->exists();
    }

    public function delete(User $user, PhotoVerification $photoVerification): bool
    {
        return $photoVerification->user_id === $user->id;
    }

    public function deletePhoto(User $user): bool
    {
        return $user->providerProfile()->exists();
    }
}
