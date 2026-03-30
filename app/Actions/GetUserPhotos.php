<?php

namespace App\Actions;

use App\Models\ProfileImage;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class GetUserPhotos
{
    public function execute(?User $user): array
    {
        if (! $user) {
            abort(403);
        }

        Gate::authorize('viewAny', ProfileImage::class);

        $photos = ProfileImage::query()
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return [
            'photos' => $photos,
        ];
    }
}
