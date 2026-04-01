<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\User;
use App\Models\UserVideo;
use Illuminate\Support\Facades\Storage;

class DeleteUserVideo
{
    public function execute(User $user, UserVideo $video): ActionResult
    {
        $disk = Storage::disk('s3');

        if ($video->video_path) {
            $disk->delete($video->video_path);
        }

        $video->delete();

        return ActionResult::success([], 'Video deleted successfully.');
    }
}
