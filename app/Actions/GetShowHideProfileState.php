<?php

namespace App\Actions;

use App\Models\HideShowProfile;
use App\Models\User;

class GetShowHideProfileState
{
    public function execute(?User $user): array
    {
        $status = false;

        if ($user) {
            $profileVisibility = HideShowProfile::query()
                ->where('user_id', $user->id)
                ->first();

            $status = $profileVisibility && $profileVisibility->status === 'show';
        }

        return [
            'status' => $status,
        ];
    }
}
