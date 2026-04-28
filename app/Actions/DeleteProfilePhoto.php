<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\ProfileImage;
use App\Models\ProviderProfile;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteProfilePhoto
{
    public function execute(ProviderProfile $profile, ProfileImage $photo): ActionResult
    {
        if ((int) $photo->provider_profile_id !== (int) $profile->id) {
            return ActionResult::authorizationFailure('You can only modify your own photos.');
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk(config('media.upload_disk'));

        $imagePath = $photo->image_path;
        $thumbnailPath = $photo->thumbnail_path;
        $wasPrimary = (bool) $photo->is_primary;

        DB::transaction(function () use ($photo, $profile, $wasPrimary) {
            // Lock all profile photos to serialize concurrent primary-photo changes
            ProfileImage::where('provider_profile_id', $profile->id)
                ->lockForUpdate()
                ->get();

            $photo->delete();

            if ($wasPrimary) {
                $nextPhoto = ProfileImage::where('provider_profile_id', $profile->id)
                    ->latest()
                    ->first();

                if ($nextPhoto) {
                    $nextPhoto->update([
                        'is_primary' => true,
                    ]);
                }
            }
        });

        if ($imagePath) {
            $disk->delete($imagePath);
        }

        if ($thumbnailPath) {
            $disk->delete($thumbnailPath);
        }

        return ActionResult::success([], 'Photo deleted successfully.');
    }
}
