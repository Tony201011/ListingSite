<?php

namespace App\Policies;

use App\Models\Tour;
use App\Models\User;

class TourPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->providerProfile()->exists();
    }

    public function create(User $user): bool
    {
        return $user->providerProfile()->exists();
    }

    public function view(User $user, Tour $tour): bool
    {
        return $tour->user_id === $user->id;
    }

    public function update(User $user, Tour $tour): bool
    {
        return $tour->user_id === $user->id;
    }

    public function delete(User $user, Tour $tour): bool
    {
        return $tour->user_id === $user->id;
    }
}
