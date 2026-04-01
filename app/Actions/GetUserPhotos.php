<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\ProfileImage;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class GetUserPhotos
{
    public function execute(?User $user): ActionResult
    {
        if (! $user) {
            return ActionResult::authorizationFailure('Unauthenticated.', 401);
        }

        Gate::authorize('viewAny', ProfileImage::class);

        $photos = ProfileImage::query()
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return ActionResult::success([
            'photos' => $photos,
        ]);
    }
}
