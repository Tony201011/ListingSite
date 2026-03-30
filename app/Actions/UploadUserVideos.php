<?php

namespace App\Actions;

use App\Models\User;
use App\Models\UserVideo;
use App\Services\UserVideoStorageService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class UploadUserVideos
{
    public function __construct(
        private UserVideoStorageService $videoStorageService
    ) {
    }

    public function execute(?User $user, array $videos): array
    {
        if (! $user) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        try {
            Gate::forUser($user)->authorize('create', UserVideo::class);
        } catch (AuthorizationException) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $username = $this->buildUsername($user);
        $uploadedVideos = [];

        foreach ($videos as $video) {
            if (! $video instanceof UploadedFile) {
                continue;
            }

            try {
                $storedVideo = $this->videoStorageService->store(
                    user: $user,
                    video: $video,
                    username: $username
                );

                $userVideo = UserVideo::create([
                    'user_id' => $user->id,
                    'video_path' => $storedVideo['video_path'],
                    'original_name' => $video->getClientOriginalName(),
                ]);

                $uploadedVideos[] = [
                    'id' => $userVideo->id,
                    'video_path' => $userVideo->video_path,
                    'video_url' => $storedVideo['video_url'],
                    'original_name' => $userVideo->original_name,
                    'created_at' => $userVideo->created_at,
                ];
            } catch (Throwable $e) {
                Log::error('Video upload failed.', [
                    'user_id' => $user->id,
                    'original_name' => $video->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ]);

                return $this->errorResponse(
                    'Failed to upload one or more videos.',
                    500
                );
            }
        }

        return [
            'status' => 200,
            'data' => [
                'message' => 'Videos uploaded successfully.',
                'videos' => $uploadedVideos,
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
