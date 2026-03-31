<?php

namespace App\Actions;

use App\Models\Rate;
use App\Models\User;

class DeleteRate
{
    public function execute(User $user, Rate $rate): void
    {
        $rate->delete();
    }
}
