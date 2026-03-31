<?php

namespace App\Actions;

use App\Models\ProfileImage;
use App\Models\User;
use App\Services\UserPhotoStorageService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
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

        $photos = array_values(array_filter($photos));

        if ($photos === []) {
            return $this->errorResponse('No photos were provided.', 422);
        }

        $username = $this->buildUsername($user);
        $uploadedPhotos = [];
        $storedBatchFiles = [];

        try {
            DB::beginTransaction();

            $hasPrimary = ProfileImage::query()
                ->where('user_id', $user->id)
                ->where('is_primary', true)
                ->lockForUpdate()
                ->exists();

            foreach ($photos as $photo) {
                $storedPhoto = $this->photoStorageService->store(
                    user: $user,
                    photo: $photo,
                    username: $username
                );

                $storedBatchFiles[] = [
                    'image_path' => $storedPhoto['image_path'],
                    'thumbnail_path' => $storedPhoto['thumbnail_path'],
                ];

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
            }

            DB::commit();

            return [
                'status' => 200,
                'data' => [
                    'message' => 'Photos uploaded successfully.',
                    'photos' => $uploadedPhotos,
                ],
            ];
        } catch (Throwable $e) {
            DB::rollBack();

            foreach ($storedBatchFiles as $storedFile) {
                try {
                    $this->photoStorageService->deletePaths(
                        $storedFile['image_path'] ?? null,
                        $storedFile['thumbnail_path'] ?? null
                    );
                } catch (Throwable $cleanupException) {
                    Log::warning('Failed to clean up uploaded photo after batch failure.', [
                        'user_id' => $user->id,
                        'image_path' => $storedFile['image_path'] ?? null,
                        'thumbnail_path' => $storedFile['thumbnail_path'] ?? null,
                        'error' => $cleanupException->getMessage(),
                    ]);
                }
            }

            Log::error('Photo upload batch failed.', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse(
                'Failed to upload photos. No changes were saved.',
                500
            );
        }
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
