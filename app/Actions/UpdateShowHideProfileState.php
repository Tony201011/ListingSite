<?php

namespace App\Actions;

use App\Models\HideShowProfile;
use App\Models\User;

class UpdateShowHideProfileState
{
    public function execute(User $user, string $status): array
    {
        $profileVisibility = HideShowProfile::updateOrCreate(
            ['user_id' => $user->id],
            ['status' => $status]
        );

        return [
            'status' => 200,
            'data' => [
                'success' => true,
                'status' => $profileVisibility->status,
                'message' => $status === 'show'
                    ? 'Your profile is now visible'
                    : 'Your profile is now hidden',
            ],
        ];
    }
}
