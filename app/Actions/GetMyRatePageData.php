<?php

namespace App\Actions;

use App\Models\ProviderProfile;

class GetMyRatePageData
{
    public function execute(?ProviderProfile $profile): array
    {
        $rates = $profile
            ? $profile->rates()
                ->whereNull('group_id')
                ->whereNotNull('description')
                ->where('description', '!=', '')
                ->orderByDesc('created_at')
                ->get()
            : collect();

        $groups = $profile
            ? $profile->rateGroups()->with(['rates' => fn ($q) => $q->orderByDesc('created_at')])->get()
            : collect();

        return [
            'rates' => $rates,
            'groups' => $groups,
        ];
    }
}
