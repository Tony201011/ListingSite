<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class DeletePhotoVerificationPhoto
{
    public function execute(User $user, string $path): array
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('s3');

        $verifications = $user->photoVerification()
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

                return [
                    'status' => 200,
                    'data' => [
                        'message' => 'Photo deleted successfully.',
                    ],
                ];
            }
        }

        return [
            'status' => 404,
            'data' => [
                'message' => 'Photo not found.',
            ],
        ];
    }
}
