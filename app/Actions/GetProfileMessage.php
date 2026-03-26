<?php

namespace App\Actions;

use App\Models\User;

class GetProfileMessage
{
    public function execute(?User $user): ?string
    {
        return $user?->profileMessage?->message;
    }
}
