<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\ProfileImage;
use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteProfilePhoto
{
    public function execute(User $user, ProfileImage $photo): ActionResult
    {
        if ((int) $photo->user_id !== (int) $user->id) {
            return ActionResult::authorizationFailure('You can only modify your own photos.');
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('s3');

        $imagePath = $photo->image_path;
        $thumbnailPath = $photo->thumbnail_path;
        $wasPrimary = (bool) $photo->is_primary;

        DB::transaction(function () use ($photo, $user, $wasPrimary) {
            // Lock all user photos to serialize concurrent primary-photo changes
            ProfileImage::where('user_id', $user->id)
                ->lockForUpdate()
                ->get();

            $photo->delete();

            if ($wasPrimary) {
                $nextPhoto = ProfileImage::where('user_id', $user->id)
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
