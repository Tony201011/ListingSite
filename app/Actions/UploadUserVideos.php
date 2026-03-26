<?php

namespace App\Actions;

use App\Models\User;
use App\Models\UserVideo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadUserVideos
{
    public function execute(?User $user, array $videos): array
    {
        if (! $user) {
            return [
                'status' => 401,
                'data' => [
                    'message' => 'Unauthenticated.',
                ],
            ];
        }

        $disk = Storage::disk('s3');
        $baseName = $user->name ?: 'user';
        $username = Str::slug($baseName) . $user->id;

        $uploadedVideos = [];

        foreach ($videos as $video) {
            if (! $video instanceof UploadedFile) {
                continue;
            }

            $originalExtension = strtolower($video->getClientOriginalExtension() ?: 'mp4');
            $baseFileName = 'profile_video_' . $user->id . '_' . Str::uuid();
            $videoFileName = $baseFileName . '.' . $originalExtension;
            $videoPath = "videos/{$username}/{$videoFileName}";

            $uploaded = $disk->putFileAs(
                "videos/{$username}",
                $video,
                $videoFileName,
                [
                    'visibility' => 'public',
                    'ContentType' => $video->getMimeType(),
                ]
            );

            if (! $uploaded) {
                Log::error('Video upload failed while storing to disk.', [
                    'user_id' => $user->id,
                    'original_name' => $video->getClientOriginalName(),
                    'target_path' => $videoPath,
                ]);

                return [
                    'status' => 500,
                    'data' => [
                        'message' => 'Failed to upload one of the videos to storage.',
                    ],
                ];
            }

            $profileVideo = UserVideo::create([
                'user_id' => $user->id,
                'video_path' => $videoPath,
                'original_name' => $video->getClientOriginalName(),
            ]);

            $uploadedVideos[] = [
                'id' => $profileVideo->id,
                'video_path' => $profileVideo->video_path,
                'video_url' => $disk->url($profileVideo->video_path),
                'original_name' => $profileVideo->original_name,
                'created_at' => $profileVideo->created_at,
            ];
        }

        return [
            'status' => 200,
            'data' => [
                'message' => 'Videos uploaded successfully.',
                'videos' => $uploadedVideos,
            ],
        ];
    }
}
