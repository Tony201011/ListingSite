<?php

namespace App\Actions;

use App\Models\User;

class GetMyRatePageData
{
    public function execute(?User $user): array
    {
        $rates = $user
            ? $user->rates()
                ->whereNull('group_id')
                ->whereNotNull('description')
                ->where('description', '!=', '')
                ->orderByDesc('created_at')
                ->get()
            : collect();

        $groups = $user
            ? $user->rateGroups()->with(['rates' => fn ($q) => $q->orderByDesc('created_at')])->get()
            : collect();

        return [
            'rates' => $rates,
            'groups' => $groups,
        ];
    }
}
