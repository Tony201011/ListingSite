<?php

namespace App\Actions;

use App\Models\ProfileImage;
use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteProfilePhoto
{
    public function execute(User $user, ProfileImage $photo): array
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('s3');

        $imagePath = $photo->image_path;
        $thumbnailPath = $photo->thumbnail_path;
        $wasPrimary = (bool) $photo->is_primary;

        DB::transaction(function () use ($photo, $user, $wasPrimary) {
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

        return [
            'status' => 200,
            'data' => [
                'message' => 'Photo deleted successfully.',
            ],
        ];
    }
}

