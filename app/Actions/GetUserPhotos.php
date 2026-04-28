<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\ProfileImage;
use App\Models\ProviderProfile;
use Illuminate\Support\Facades\Gate;

class GetUserPhotos
{
    public function execute(?ProviderProfile $profile): ActionResult
    {
        if (! $profile) {
            return ActionResult::authorizationFailure('Unauthenticated.', 401);
        }

        Gate::authorize('viewAny', ProfileImage::class);

        $photos = ProfileImage::query()
            ->where('provider_profile_id', $profile->id)
            ->latest()
            ->get();

        return ActionResult::success([
            'photos' => $photos,
        ]);
    }
}
