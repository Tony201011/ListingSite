<?php

namespace App\Actions;
use App\Models\User;
use App\Models\UserVideo;
use Illuminate\Support\Facades\Gate;

class GetUserVideos
{
    public function execute(?User $user): array
    {
        if (! $user) {
            abort(403);
        }

        Gate::authorize('viewAny', UserVideo::class);

        $videos = UserVideo::query()
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return [
            'videos' => $videos,
        ];
    }
}
