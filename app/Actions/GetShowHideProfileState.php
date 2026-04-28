<?php

namespace App\Actions;

use App\Models\HideShowProfile;
use App\Models\ProviderProfile;

class GetShowHideProfileState
{
    public function execute(?ProviderProfile $profile): array
    {
        $status = false;

        if ($profile) {
            $profileVisibility = HideShowProfile::query()
                ->where('provider_profile_id', $profile->id)
                ->first();

            $status = $profileVisibility && $profileVisibility->status === 'show';
        }

        return [
            'status' => $status,
        ];
    }
}
