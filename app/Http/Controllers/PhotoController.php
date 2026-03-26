<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadPhotosRequest;
use App\Models\ProfileImage;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Throwable;

class PhotoController extends Controller
{
    public function index()
    {
        return view('add-photo');
    }

    public function getPhotos()
    {
        $photos = ProfileImage::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('photos', compact('photos'));
    }

    public function uploadPhotos(UploadPhotosRequest $request): JsonResponse
    {
        try {
            /** @var \App\Models\User|null $user */
            $user = Auth::user();

            if (! $user) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            /** @var FilesystemAdapter $disk */
            $disk = Storage::disk('s3');

            $baseName = $user->name ?: 'user';
            $username = Str::slug($baseName) . $user->id;

            $uploadedPhotos = [];

            foreach ($request->file('photos', []) as $index => $photo) {
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
                    return response()->json([
                        'message' => 'Failed to upload original image to storage.',
                    ], 500);
                }

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
                    return response()->json([
                        'message' => 'Failed to upload thumbnail to storage.',
                    ], 500);
                }

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
            }

            return response()->json([
                'message' => 'Photos uploaded successfully.',
                'photos' => $uploadedPhotos,
            ]);
        } catch (Throwable $e) {
            Log::error('Photo upload failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'message' => 'Upload failed.',
                'error' => config('app.debug')
                    ? $e->getMessage()
                    : 'Something went wrong while uploading photos.',
            ], 500);
        }
    }

    public function setCover(ProfileImage $photo): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user || $photo->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        ProfileImage::where('user_id', $user->id)->update([
            'is_primary' => false,
        ]);

        $photo->update([
            'is_primary' => true,
        ]);

        return response()->json([
            'message' => 'Cover photo updated successfully.',
        ]);
    }

    public function destroy(ProfileImage $photo): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user || $photo->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('s3');

        if ($photo->image_path) {
            $disk->delete($photo->image_path);
        }

        if ($photo->thumbnail_path) {
            $disk->delete($photo->thumbnail_path);
        }

        $wasPrimary = $photo->is_primary;

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

        return response()->json([
            'message' => 'Photo deleted successfully.',
        ]);
    }
}
