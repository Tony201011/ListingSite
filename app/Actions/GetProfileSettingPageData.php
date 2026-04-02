<?php

namespace App\Actions;

use App\Models\Category;
use App\Models\User;
use App\Models\UserVideo;

class GetProfileSettingPageData
{
    public function execute(?User $user): array
    {
        $user = $user?->load('providerProfile');
        $profile = $user?->providerProfile;

        $ids = array_filter([
            $profile?->age_group_id,
            $profile?->hair_color_id,
            $profile?->hair_length_id,
            $profile?->ethnicity_id,
            $profile?->body_type_id,
            $profile?->bust_size_id,
            $profile?->your_length_id,
        ]);

        $categories = Category::query()
            ->whereIn('id', $ids)
            ->pluck('name', 'id');

        $userInfo = [
            'user' => $user,
            'provider_profile' => $profile,
            'age_group_name' => $categories[$profile?->age_group_id] ?? null,
            'hair_color_name' => $categories[$profile?->hair_color_id] ?? null,
            'hair_length_name' => $categories[$profile?->hair_length_id] ?? null,
            'ethnicity_name' => $categories[$profile?->ethnicity_id] ?? null,
            'body_type_name' => $categories[$profile?->body_type_id] ?? null,
            'bust_size_name' => $categories[$profile?->bust_size_id] ?? null,
            'your_length_name' => $categories[$profile?->your_length_id] ?? null,
        ];

        $profileImages = $user?->profileImages()
            ->latest()
            ->get() ?? collect();

        $videos = $user
            ? UserVideo::query()
                ->where('user_id', $user->id)
                ->latest()
                ->get()
            : collect();

        $photoVerification = $user?->photoVerification()
            ->where('status', 'approved')
            ->whereNull('deleted_at')
            ->count() > 1;

        return [
            'profileImages' => $profileImages,
            'videos' => $videos,
            'photoVerification' => $photoVerification,
            'userInfo' => $userInfo,
        ];
    }
}
