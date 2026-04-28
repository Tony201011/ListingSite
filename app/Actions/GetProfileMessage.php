<?php

namespace App\Actions;

use App\Models\ProviderProfile;

class GetProfileMessage
{
    public function execute(?ProviderProfile $profile): ?string
    {
        return $profile?->profileMessage?->message;
    }
}
