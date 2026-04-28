<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\ProfileImage;
use App\Models\ProviderProfile;
use Illuminate\Support\Facades\DB;

class SetPrimaryProfilePhoto
{
    public function execute(ProviderProfile $profile, ProfileImage $photo): ActionResult
    {
        if ((int) $photo->provider_profile_id !== (int) $profile->id) {
            return ActionResult::authorizationFailure('You can only modify your own photos.');
        }

        DB::transaction(function () use ($profile, $photo) {
            // Lock all profile photos to serialize concurrent primary-photo changes
            ProfileImage::where('provider_profile_id', $profile->id)
                ->lockForUpdate()
                ->get();

            ProfileImage::where('provider_profile_id', $profile->id)->update([
                'is_primary' => false,
            ]);

            // Use query builder to ensure the UPDATE always runs, even when
            // the in-memory model already has is_primary = true (stale after
            // the bulk update above).
            ProfileImage::where('id', $photo->id)->update([
                'is_primary' => true,
            ]);
        });

        return ActionResult::success([], 'Cover photo updated successfully.');
    }
}
