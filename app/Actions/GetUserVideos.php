<?php

namespace App\Actions;

use App\Models\UserVideo;

class GetUserVideos
{
    public function execute(?int $userId): array
    {
        $videos = $userId
            ? UserVideo::where('user_id', $userId)->latest()->get()
            : collect();

        return [
            'videos' => $videos,
        ];
    }
}
