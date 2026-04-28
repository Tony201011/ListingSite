<?php

namespace App\Actions;

use App\Models\ProviderProfile;
use App\Models\User;

class GetActiveProviderProfile
{
    public function execute(User $user): ?ProviderProfile
    {
        $profileId = session('active_provider_profile_id');

        if ($profileId) {
            $profile = $user->providerProfiles()->find($profileId);

            if ($profile) {
                return $profile;
            }
        }

        return $user->providerProfiles()->orderBy('id')->first();
    }
}
