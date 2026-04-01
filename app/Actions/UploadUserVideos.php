<?php

namespace App\Actions;

use App\Models\User;
use App\Models\UserVideo;
use App\Services\UserVideoStorageService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class UploadUserVideos
{
    public function __construct(
        private UserVideoStorageService $videoStorageService
    ) {}

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

        $videos = array_values(array_filter(
            $videos,
            fn ($video) => $video instanceof UploadedFile
        ));

        if ($videos === []) {
            return $this->errorResponse('No videos were provided.', 422);
        }

        $username = $this->buildUsername($user);
        $uploadedVideos = [];
        $storedVideoPaths = [];

        try {
            DB::beginTransaction();

            foreach ($videos as $video) {
                $storedVideo = $this->videoStorageService->store(
                    user: $user,
                    video: $video,
                    username: $username
                );

                $storedVideoPaths[] = $storedVideo['video_path'];

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
            }

            DB::commit();

            return [
                'status' => 200,
                'data' => [
                    'message' => 'Videos uploaded successfully.',
                    'videos' => $uploadedVideos,
                ],
            ];
        } catch (Throwable $e) {
            DB::rollBack();

            foreach ($storedVideoPaths as $videoPath) {
                try {
                    $this->videoStorageService->deletePath($videoPath);
                } catch (Throwable $cleanupException) {
                    Log::warning('Failed to clean up uploaded video after batch failure.', [
                        'user_id' => $user->id,
                        'video_path' => $videoPath,
                        'error' => $cleanupException->getMessage(),
                    ]);
                }
            }

            Log::error('Video upload batch failed.', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse(
                'Failed to upload videos. No changes were saved.',
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

        return $slug.$user->id;
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
