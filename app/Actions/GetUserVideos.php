<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\ProviderProfile;
use App\Models\UserVideo;
use Illuminate\Support\Facades\Gate;

class GetUserVideos
{
    public function execute(?ProviderProfile $profile): ActionResult
    {
        if (! $profile) {
            return ActionResult::authorizationFailure('Unauthenticated.', 401);
        }

        Gate::forUser($profile->user)->authorize('viewAny', UserVideo::class);

        $videos = UserVideo::query()
            ->where('provider_profile_id', $profile->id)
            ->latest()
            ->get();

        return ActionResult::success([
            'videos' => $videos,
        ]);
    }
}
