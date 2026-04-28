<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\ProviderProfile;
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

    public function execute(?ProviderProfile $profile, array $videos): ActionResult
    {
        if (! $profile) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        $user = $profile->user;

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

        $username = $this->buildUsername($profile);
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
                    'user_id' => $profile->user_id,
                    'provider_profile_id' => $profile->id,
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

            return ActionResult::success([
                'videos' => $uploadedVideos,
            ], 'Videos uploaded successfully.');
        } catch (Throwable $e) {
            DB::rollBack();

            foreach ($storedVideoPaths as $videoPath) {
                try {
                    $this->videoStorageService->deletePath($videoPath);
                } catch (Throwable $cleanupException) {
                    Log::warning('Failed to clean up uploaded video after batch failure.', [
                        'profile_id' => $profile->id,
                        'video_path' => $videoPath,
                        'error' => $cleanupException->getMessage(),
                    ]);
                }
            }

            Log::error('Video upload batch failed.', [
                'profile_id' => $profile->id,
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse(
                'Failed to upload videos. No changes were saved.',
                500
            );
        }
    }

    private function buildUsername(ProviderProfile $profile): string
    {
        $baseName = trim((string) ($profile->name ?: 'user'));
        $slug = Str::slug($baseName);

        if ($slug === '') {
            $slug = 'user';
        }

        return $slug.$profile->id;
    }

    private function errorResponse(string $message, int $status): ActionResult
    {
        return $status >= 500
            ? ActionResult::infrastructureFailure($message, $status)
            : ActionResult::domainError($message, status: $status);
    }
}
