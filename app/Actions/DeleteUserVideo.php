<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\ProviderProfile;
use App\Models\UserVideo;
use Illuminate\Support\Facades\Storage;

class DeleteUserVideo
{
    public function execute(ProviderProfile $profile, UserVideo $video): ActionResult
    {
        if ((int) $video->provider_profile_id !== (int) $profile->id) {
            return ActionResult::authorizationFailure('You can only modify your own videos.');
        }

        $disk = Storage::disk(config('media.upload_disk'));

        if ($video->video_path) {
            $disk->delete($video->video_path);
        }

        $video->delete();

        return ActionResult::success([], 'Video deleted successfully.');
    }
}
