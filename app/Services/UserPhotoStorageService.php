<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use InvalidArgumentException;
use Throwable;

class UserPhotoStorageService
{
    public function store(User $user, UploadedFile $photo, string $username): array
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('s3');

        $extension = strtolower($photo->extension() ?: $photo->getClientOriginalExtension() ?: 'jpg');
        $baseFileName = 'profile_' . $user->id . '_' . Str::uuid();

        $imageFileName = $baseFileName . '.' . $extension;
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
                    'ContentType' => $photo->getMimeType() ?: 'application/octet-stream',
                ]
            );

            if (! $uploaded) {
                throw new InvalidArgumentException('Failed to upload original image to storage.');
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
                throw new InvalidArgumentException('Failed to upload thumbnail to storage.');
            }

            $uploadedThumbnailPath = $thumbnailPath;

            return [
                'image_path' => $imagePath,
                'thumbnail_path' => $thumbnailPath,
                'image_url' => $disk->url($imagePath),
                'thumbnail_url' => $disk->url($thumbnailPath),
            ];
        } catch (Throwable $e) {
            $this->deletePaths($uploadedImagePath, $uploadedThumbnailPath);

            throw $e;
        }
    }

    public function deletePaths(?string $imagePath, ?string $thumbnailPath): void
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('s3');

        if ($imagePath) {
            $disk->delete($imagePath);
        }

        if ($thumbnailPath) {
            $disk->delete($thumbnailPath);
        }
    }
}
