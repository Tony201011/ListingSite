<?php

namespace App\Actions;

use App\Models\ProfileImage;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SetPrimaryProfilePhoto
{
    public function execute(User $user, ProfileImage $photo): array
    {
        DB::transaction(function () use ($user, $photo) {
            // Lock all user photos to serialize concurrent primary-photo changes
            ProfileImage::where('user_id', $user->id)
                ->lockForUpdate()
                ->get();

            ProfileImage::where('user_id', $user->id)->update([
                'is_primary' => false,
            ]);

            // Use query builder to ensure the UPDATE always runs, even when
            // the in-memory model already has is_primary = true (stale after
            // the bulk update above).
            ProfileImage::where('id', $photo->id)->update([
                'is_primary' => true,
            ]);
        });

        return [
            'status' => 200,
            'data' => [
                'message' => 'Cover photo updated successfully.',
            ],
        ];
    }
}
