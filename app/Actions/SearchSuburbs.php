<?php

namespace App\Actions;

use App\Models\City;
use App\Models\Postcode;
use Illuminate\Support\Facades\DB;

class SearchSuburbs
{
    private const INVALID_TEXT_VALUES = [
        'null',
        'undefined',
    ];

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
            ->map(function (City $city): ?array {
                $stateName = $city->state?->name ?? '';
                $stateAbbr = self::STATE_ABBREVIATIONS[$stateName] ?? $stateName;

                return $this->formatResult(
                    suburb: $city->name,
                    state: $stateAbbr,
                    postcode: null
                );
            })
            ->filter()
            ->values()
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
            ->map(fn (Postcode $postcode): ?array => $this->formatResult(
                suburb: $postcode->suburb,
                state: $postcode->state,
                postcode: $postcode->postcode
            ))
            ->filter()
            ->values()
            ->all();

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

        return array_slice($results, 0, 20);
    }

    private function formatResult(mixed $suburb, mixed $state, mixed $postcode): ?array
    {
        $sanitizedSuburb = $this->sanitizeText($suburb);
        $sanitizedState = $this->sanitizeText($state);

        if ($sanitizedSuburb === null || $sanitizedState === null) {
            return null;
        }

        return [
            'suburb' => $sanitizedSuburb,
            'state' => $sanitizedState,
            'postcode' => $this->sanitizePostcode($postcode),
        ];
    }

    private function sanitizeText(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        if ($normalized === '' || in_array(strtolower($normalized), self::INVALID_TEXT_VALUES, true)) {
            return null;
        }

        return $normalized;
    }

    private function sanitizePostcode(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        if ($normalized === '' || in_array(strtolower($normalized), self::INVALID_TEXT_VALUES, true)) {
            return null;
        }

        return preg_match('/^\d{4}$/', $normalized) === 1
            ? $normalized
            : null;
    }
}
