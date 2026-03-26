<?php

namespace App\Actions;

use App\Models\HideShowProfile;
use App\Models\User;

class UpdateShowHideProfileState
{
    public function execute(?User $user, string $status): array
    {
        if (! $user) {
            return [
                'status' => 401,
                'data' => [
                    'success' => false,
                    'message' => 'User not authenticated.',
                ],
            ];
        }

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
