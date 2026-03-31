<?php

namespace App\Actions;

use App\Models\ShortUrl;
use App\Models\User;

class UpdateUserShortUrl
{
    public function execute(User $user, string $slug): array
    {
        ShortUrl::query()->updateOrCreate(
            ['user_id' => $user->id],
            ['short_url' => $slug]
        );

        return [
            'status' => 200,
            'data' => [
                'success' => true,
                'message' => 'Short URL updated successfully.',
                'slug' => $slug,
            ],
        ];
    }
}
