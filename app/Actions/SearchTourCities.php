<?php

namespace App\Actions;

use App\Models\TourCity;

class SearchTourCities
{
    public function execute(?string $query): array
    {
        $query = trim((string) $query);

        if ($query === '' || mb_strlen($query) < 2) {
            return [];
        }

        return TourCity::query()
            ->where('name', 'like', $query . '%')
            ->orderBy('name')
            ->get(['name', 'state'])
            ->map(fn ($city) => [
                'name' => $city->name,
                'adminName1' => $city->state,
            ])
            ->values()
            ->all();
    }
}
