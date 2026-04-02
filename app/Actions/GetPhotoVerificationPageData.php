<?php

namespace App\Actions;

use App\Models\User;
use App\Models\VerificationExampleImage;

class GetPhotoVerificationPageData
{
    public function execute(?User $user): array
    {
        $latestVerification = null;
        $lastTwoPhotos = [];

        if ($user) {
            $latestVerifications = $user->photoVerification()
                ->whereNull('deleted_at')
                ->latest('created_at')
                ->take(2)
                ->get();

            if ($latestVerifications->isNotEmpty()) {
                $latestVerification = $latestVerifications->first();

                $lastTwoPhotos = $latestVerifications
                    ->map(function ($verification) {
                        $photos = is_array($verification->photos)
                            ? $verification->photos
                            : json_decode($verification->photos, true);

                        if (! is_array($photos)) {
                            $photos = [];
                        }

                        return collect($photos)->map(function ($photo) use ($verification) {
                            $photo['status'] = $verification->status;
                            $photo['admin_note'] = $verification->admin_note;

                            return $photo;
                        });
                    })
                    ->flatten(1)
                    ->take(2)
                    ->values()
                    ->toArray();
            }
        }

        $exampleImages = VerificationExampleImage::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return [
            'latestVerification' => $latestVerification,
            'lastTwoPhotos' => $lastTwoPhotos,
            'exampleImages' => $exampleImages,
        ];
    }
}
