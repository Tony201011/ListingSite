<?php

namespace App\Actions;

use App\Models\User;
use App\Models\UserVideo;
use Illuminate\Support\Facades\Storage;

class DeleteUserVideo
{
    public function execute(User $user, UserVideo $video): array
    {
        $disk = Storage::disk('s3');

        if ($video->video_path) {
            $disk->delete($video->video_path);
        }

        $video->delete();

        return [
            'status' => 200,
            'data' => [
                'message' => 'Video deleted successfully.',
            ],
        ];
    }
}
