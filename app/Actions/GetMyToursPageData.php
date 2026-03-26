<?php

namespace App\Actions;

use App\Models\User;

class GetMyToursPageData
{
    public function execute(?User $user): array
    {
        $tours = $user
            ? $user->tours()->orderBy('from')->get()
            : collect();

        return [
            'tours' => $tours,
        ];
    }
}
