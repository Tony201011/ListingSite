<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\ShortUrl;
use App\Models\User;

class UpdateUserShortUrl
{
    public function execute(User $user, string $slug): ActionResult
    {
        ShortUrl::query()->updateOrCreate(
            ['user_id' => $user->id],
            ['short_url' => $slug]
        );

        return ActionResult::success([
            'slug' => $slug,
        ], 'Short URL updated successfully.');
    }
}
