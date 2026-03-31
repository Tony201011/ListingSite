<?php

namespace App\Policies;

use App\Models\ShortUrl;
use App\Models\User;

class ShortUrlPolicy
{
    public function view(User $user, ShortUrl $shortUrl): bool
    {
        return $shortUrl->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->providerProfile()->exists();
    }

    public function update(User $user, ShortUrl $shortUrl): bool
    {
        return $shortUrl->user_id === $user->id;
    }
}
