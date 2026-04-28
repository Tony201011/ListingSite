<?php

namespace App\Actions;

use App\Models\ProviderProfile;

class GetMyToursPageData
{
    public function execute(?ProviderProfile $profile): array
    {
        $tours = $profile
            ? $profile->tours()->orderBy('from')->get()
            : collect();

        return [
            'tours' => $tours,
        ];
    }
}
