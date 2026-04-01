<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\HideShowProfile;
use App\Models\User;

class UpdateShowHideProfileState
{
    public function execute(User $user, string $status): ActionResult
    {
        $profileVisibility = HideShowProfile::updateOrCreate(
            ['user_id' => $user->id],
            ['status' => $status]
        );

        return ActionResult::success([
            'status' => $profileVisibility->status,
        ], $status === 'show'
            ? 'Your profile is now visible'
            : 'Your profile is now hidden');
    }
}
