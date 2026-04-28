<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\HideShowProfile;
use App\Models\ProviderProfile;

class UpdateShowHideProfileState
{
    public function execute(ProviderProfile $profile, string $status): ActionResult
    {
        $profileVisibility = HideShowProfile::updateOrCreate(
            ['provider_profile_id' => $profile->id],
            ['user_id' => $profile->user_id, 'status' => $status]
        );

        return ActionResult::success([
            'status' => $profileVisibility->status,
        ], $status === 'show'
            ? 'Your profile is now visible'
            : 'Your profile is now hidden');
    }
}
