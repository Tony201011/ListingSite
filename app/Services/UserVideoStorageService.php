<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class UserVideoStorageService
{
    public function store(User $user, UploadedFile $video, string $username): array
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk(config('media.upload_disk'));

        $extension = strtolower($video->extension() ?: $video->getClientOriginalExtension() ?: 'mp4');
        $baseFileName = 'profile_video_'.$user->id.'_'.Str::uuid();
        $videoFileName = $baseFileName.'.'.$extension;
        $videoPath = "videos/{$username}/{$videoFileName}";

        $uploadedVideoPath = null;

        try {
            $uploaded = $disk->putFileAs(
                "videos/{$username}",
                $video,
                $videoFileName,
                [
                    'visibility' => 'public',
                    'ContentType' => $video->getMimeType() ?: 'application/octet-stream',
                ]
            );

            if (! $uploaded) {
                throw new RuntimeException('Failed to upload video to storage.');
            }

            $uploadedVideoPath = $videoPath;

            return [
                'video_path' => $videoPath,
                'video_url' => $disk->url($videoPath),
            ];
        } catch (Throwable $e) {
            $this->deletePath($uploadedVideoPath);

            throw $e;
        }
    }

    public function deletePath(?string $videoPath): void
    {
        if (! $videoPath) {
            return;
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk(config('media.upload_disk'));

        $disk->delete($videoPath);
    }
}
