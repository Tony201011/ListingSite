<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\User;
use App\Models\UserVideo;
use Illuminate\Support\Facades\Gate;

class GetUserVideos
{
    public function execute(?User $user): ActionResult
    {
        if (! $user) {
            return ActionResult::authorizationFailure('Unauthenticated.', 401);
        }

        Gate::forUser($user)->authorize('viewAny', UserVideo::class);

        $videos = UserVideo::query()
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return ActionResult::success([
            'videos' => $videos,
        ]);
    }
}
