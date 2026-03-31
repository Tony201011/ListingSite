<?php

namespace App\Actions;

use App\Models\RateGroup;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteRateGroup
{
    public function execute(User $user, RateGroup $group): void
    {
        DB::transaction(function () use ($group) {
            $group->rates()->update(['group_id' => null]);
            $group->delete();
        });
    }
}
