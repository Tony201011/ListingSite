<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\ProviderProfile;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class DeletePhotoVerificationPhoto
{
    public function execute(ProviderProfile $profile, string $path): ActionResult
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk(config('media.upload_disk'));

        $verifications = $profile->photoVerification()
            ->whereNull('deleted_at')
            ->get();

        foreach ($verifications as $verification) {
            $photos = is_array($verification->photos)
                ? $verification->photos
                : json_decode($verification->photos, true);

            if (! is_array($photos)) {
                $photos = [];
            }

            $updatedPhotos = collect($photos)
                ->reject(function ($photo) use ($path) {
                    return isset($photo['path']) && $photo['path'] === $path;
                })
                ->values()
                ->toArray();

            if (count($updatedPhotos) !== count($photos)) {
                $disk->delete($path);

                $verification->deleted_at = now();
                $verification->save();

                return ActionResult::success([], 'Photo deleted successfully.');
            }
        }

        return ActionResult::domainError('Photo not found.', status: 404);
    }
}
