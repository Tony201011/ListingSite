<?php

namespace App\Actions;

use App\Models\City;
use App\Models\Postcode;
use Illuminate\Support\Facades\DB;

class SearchSuburbs
{
    private const STATE_ABBREVIATIONS = [
        'Australian Capital Territory' => 'ACT',
        'New South Wales' => 'NSW',
        'Victoria' => 'VIC',
        'Queensland' => 'QLD',
        'Western Australia' => 'WA',
        'South Australia' => 'SA',
        'Tasmania' => 'TAS',
        'Northern Territory' => 'NT',
    ];

    public function execute(?string $query): array
    {
        if (! $query || strlen($query) < 2) {
            return [];
        }

        $cityResults = City::query()
            ->with('state')
            ->where('name', 'LIKE', $query.'%')
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(function (City $city): array {
                $stateName = $city->state?->name ?? '';
                $stateAbbr = self::STATE_ABBREVIATIONS[$stateName] ?? $stateName;

                return [
                    'suburb' => $city->name,
                    'state' => $stateAbbr,
                    'postcode' => null,
                ];
            })
            ->all();

        $suburbResults = Postcode::query()
            ->select([
                'suburb',
                'state',
                DB::raw('MIN(postcode) as postcode'),
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

        // Merge city results first, then suburb results, deduplicating by suburb+state
        $seen = [];
        $results = [];

        foreach (array_merge($cityResults, $suburbResults) as $item) {
            $key = strtolower($item['suburb']).','.strtolower($item['state']);
            if (! isset($seen[$key])) {
                $seen[$key] = true;
                $results[] = $item;
            }
        }

        return $results;
    }
}
