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
            ->select([
                'suburb',
                'state',
                \Illuminate\Support\Facades\DB::raw('MIN(postcode) as postcode'),
            ])
            ->where(function ($q) use ($query) {
                $q->where('suburb', 'LIKE', $query.'%')
                    ->orWhere('postcode', 'LIKE', $query.'%');
            })
            ->groupBy(['suburb', 'state'])
            ->orderBy('suburb')
            ->limit(20)
            ->get()
            ->toArray();
    }
}
