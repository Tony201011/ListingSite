<?php

namespace App\Actions;

use App\Models\AntiSpamPolicy;

class GetAntiSpamPolicyPageData
{
    public function execute(): array
    {
        $policy = AntiSpamPolicy::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();

        return [
            'policy' => $policy,
        ];
    }
}
