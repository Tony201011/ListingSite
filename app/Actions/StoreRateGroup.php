<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\ProviderProfile;

class StoreRateGroup
{
    public function execute(ProviderProfile $profile, array $validated): ActionResult
    {
        $group = $profile->rateGroups()->create([
            'user_id' => $profile->user_id,
            'name' => $validated['name'],
        ]);

        return ActionResult::success([
            'group' => $group,
        ], 'Rate group created successfully.', 201);
    }
}
