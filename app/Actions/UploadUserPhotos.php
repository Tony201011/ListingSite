<?php

namespace App\Actions;

use App\Models\ProfileImage;
use App\Models\User;
use App\Services\UserPhotoStorageService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class UploadUserPhotos
{
    public function __construct(
        private UserPhotoStorageService $photoStorageService
    ) {
    }

    public function execute(?User $user, array $photos): array
    {
        if (! $user) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        try {
            Gate::forUser($user)->authorize('create', ProfileImage::class);
        } catch (AuthorizationException) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $username = $this->buildUsername($user);

        $uploadedPhotos = [];
        $hasPrimary = ProfileImage::query()
            ->where('user_id', $user->id)
            ->where('is_primary', true)
            ->exists();

        foreach ($photos as $photo) {
            if (! $photo) {
                continue;
            }

            try {
                $storedPhoto = $this->photoStorageService->store(
                    user: $user,
                    photo: $photo,
                    username: $username
                );

                $profileImage = ProfileImage::create([
                    'user_id' => $user->id,
                    'image_path' => $storedPhoto['image_path'],
                    'thumbnail_path' => $storedPhoto['thumbnail_path'],
                    'is_primary' => ! $hasPrimary,
                ]);

                if (! $hasPrimary) {
                    $hasPrimary = true;
                }

                $uploadedPhotos[] = [
                    'id' => $profileImage->id,
                    'image_path' => $profileImage->image_path,
                    'thumbnail_path' => $profileImage->thumbnail_path,
                    'image_url' => $storedPhoto['image_url'],
                    'thumbnail_url' => $storedPhoto['thumbnail_url'],
                    'is_primary' => $profileImage->is_primary,
                ];
            } catch (Throwable $e) {
                Log::error('Photo upload failed.', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);

                return $this->errorResponse(
                    'Failed to upload one or more photos.',
                    500
                );
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

    private function buildUsername(User $user): string
    {
        $baseName = trim((string) ($user->name ?: 'user'));
        $slug = Str::slug($baseName);

        if ($slug === '') {
            $slug = 'user';
        }

        return $slug . $user->id;
    }

    private function errorResponse(string $message, int $status): array
    {
        return [
            'status' => $status,
            'data' => [
                'message' => $message,
            ],
        ];
    }
}
