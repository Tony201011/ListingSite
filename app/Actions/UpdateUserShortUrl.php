<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\ProviderProfile;
use App\Models\ShortUrl;

class UpdateUserShortUrl
{
    public function execute(ProviderProfile $profile, string $slug): ActionResult
    {
        ShortUrl::query()->updateOrCreate(
            ['provider_profile_id' => $profile->id],
            ['short_url' => $slug]
        );

        return ActionResult::success([
            'slug' => $slug,
        ], 'Short URL updated successfully.');
    }
}
