<?php

namespace App\Actions;

use App\Models\User;

class GetMyRatePageData
{
    public function execute(?User $user): array
    {
        $rates = $user
            ? $user->rates()->orderByDesc('created_at')->get()
            : collect();

        $groups = collect([
            (object) ['id' => 1, 'name' => 'Group A'],
            (object) ['id' => 2, 'name' => 'Group B'],
            (object) ['id' => 3, 'name' => 'Group C'],
        ]);

        return [
            'rates' => $rates,
            'groups' => $groups,
        ];
    }
}
