<?php

namespace App\Actions;

use App\Models\User;

class GetMyProfilePageData
{
    public function execute(?User $user): array
    {
        $profile = $user?->providerProfile;

        $stepOneCompleted = false;

        if ($profile) {
            $stepOneCompleted =
                ! empty($profile->introduction_line) &&
                ! empty($profile->profile_text) &&
                ! is_null($profile->age_group_id) &&
                ! is_null($profile->hair_color_id) &&
                ! is_null($profile->hair_length_id) &&
                ! is_null($profile->ethnicity_id) &&
                ! is_null($profile->body_type_id) &&
                ! is_null($profile->bust_size_id) &&
                ! is_null($profile->your_length_id) &&
                ! empty($profile->availability) &&
                ! empty($profile->contact_method) &&
                ! empty($profile->phone_contact_preference) &&
                ! empty($profile->time_waster_shield) &&
                ! empty($profile->primary_identity) &&
                ! empty($profile->attributes) &&
                ! empty($profile->services_style) &&
                ! empty($profile->services_provided);
        }

        $stepTwoCompleted = $user?->profileImages()
            ->whereNull('deleted_at')
            ->count() > 0;

        $stepPhotoVerificationCompleted = $user?->photoVerification()
            ->where('status', 'approved')
            ->whereNull('deleted_at')
            ->count() > 1;

        $shortUrlRecord = $user?->shortUrl;

        $profileUrl = $profile?->slug
            ? route('profile.show', ['slug' => $profile->slug])
            : null;

        $shortUrlFull = $shortUrlRecord?->short_url
            ? url($shortUrlRecord->short_url)
            : null;

        return [
            'user' => $user,
            'profile' => $profile,
            'stepOneCompleted' => $stepOneCompleted,
            'stepTwoCompleted' => $stepTwoCompleted,
            'stepPhotoVerificationCompleted' => $stepPhotoVerificationCompleted,
            'profileUrl' => $profileUrl,
            'shortUrlFull' => $shortUrlFull,
        ];
    }
}
