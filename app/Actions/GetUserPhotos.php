<?php

namespace App\Actions;

use App\Models\ProfileImage;

class GetUserPhotos
{
    public function execute(?int $userId): array
    {
        $photos = $userId
            ? ProfileImage::where('user_id', $userId)->latest()->get()
            : collect();

        return [
            'photos' => $photos,
        ];
    }
}
