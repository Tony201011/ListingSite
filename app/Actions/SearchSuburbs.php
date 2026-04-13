<?php

namespace App\Actions;

use App\Models\Postcode;

class SearchSuburbs
{
    public function execute(?string $query): array
    {
        if (! $query || strlen($query) < 2) {
            return [];
        }

        return Postcode::query()
            ->where(function ($q) use ($query) {
                $q->where('suburb', 'LIKE', $query.'%')
                    ->orWhere('postcode', 'LIKE', $query.'%')
                    ->orWhere('city_region', 'LIKE', $query.'%');
            })
            ->orderBy('suburb')
            ->get([
                'id',
                'suburb',
                'state',
                'postcode',
                'city_region',
                'latitude',
                'longitude',
            ])
            ->toArray();
    }
}
