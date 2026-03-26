<?php

namespace App\Actions;

use App\Models\ProfileImage;
use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Throwable;

class UploadUserPhotos
{
    public function execute(?User $user, array $photos): array
    {
        if (! $user) {
            return [
                'status' => 401,
                'data' => [
                    'message' => 'Unauthenticated.',
                ],
            ];
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('s3');

        $baseName = $user->name ?: 'user';
        $username = Str::slug($baseName) . $user->id;

        $uploadedPhotos = [];

        foreach ($photos as $index => $photo) {
            if (! $photo instanceof UploadedFile) {
                continue;
            }

            $hasPrimary = ProfileImage::where('user_id', $user->id)
                ->where('is_primary', true)
                ->exists();

            $isPrimary = ! $hasPrimary && $index === 0;

            $originalExtension = strtolower($photo->getClientOriginalExtension() ?: 'jpg');
            $baseFileName = 'profile_' . $user->id . '_' . Str::uuid();

            $imageFileName = $baseFileName . '.' . $originalExtension;
            $thumbFileName = $baseFileName . '_thumb.jpg';

            $imagePath = "images/{$username}/{$imageFileName}";
            $thumbnailPath = "thumbnails/{$username}/{$thumbFileName}";

            $uploadedImagePath = null;
            $uploadedThumbnailPath = null;

            try {
                $uploaded = $disk->putFileAs(
                    "images/{$username}",
                    $photo,
                    $imageFileName,
                    [
                        'visibility' => 'public',
                        'ContentType' => $photo->getMimeType(),
                    ]
                );

                if (! $uploaded) {
                    return [
                        'status' => 500,
                        'data' => [
                            'message' => 'Failed to upload original image to storage.',
                        ],
                    ];
                }

                $uploadedImagePath = $imagePath;

                $thumbnail = Image::read($photo->getRealPath())
                    ->cover(400, 400)
                    ->toJpeg(85);

                $thumbUploaded = $disk->put(
                    $thumbnailPath,
                    (string) $thumbnail,
                    [
                        'visibility' => 'public',
                        'ContentType' => 'image/jpeg',
                    ]
                );

                if (! $thumbUploaded) {
                    if ($uploadedImagePath) {
                        $disk->delete($uploadedImagePath);
                    }

                    return [
                        'status' => 500,
                        'data' => [
                            'message' => 'Failed to upload thumbnail to storage.',
                        ],
                    ];
                }

                $uploadedThumbnailPath = $thumbnailPath;

                $profileImage = ProfileImage::create([
                    'user_id' => $user->id,
                    'image_path' => $imagePath,
                    'thumbnail_path' => $thumbnailPath,
                    'is_primary' => $isPrimary,
                ]);

                $uploadedPhotos[] = [
                    'id' => $profileImage->id,
                    'image_path' => $profileImage->image_path,
                    'thumbnail_path' => $profileImage->thumbnail_path,
                    'image_url' => $disk->url($profileImage->image_path),
                    'thumbnail_url' => $disk->url($profileImage->thumbnail_path),
                    'is_primary' => $profileImage->is_primary,
                ];
            } catch (Throwable $e) {
                if ($uploadedImagePath) {
                    $disk->delete($uploadedImagePath);
                }

                if ($uploadedThumbnailPath) {
                    $disk->delete($uploadedThumbnailPath);
                }

                throw $e;
            }
        }

        return [
            'status' => 200,
            'data' => [
                'message' => 'Photos uploaded successfully.',
                'photos' => $uploadedPhotos,
            ],
        ];
    }
}
