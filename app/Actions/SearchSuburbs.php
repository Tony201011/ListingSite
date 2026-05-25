<?php

namespace App\Actions;

use App\Models\City;
use App\Models\Postcode;
use App\Models\ProviderProfile;
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

        [$onlineCityIds, $onlineSuburbKeys] = $this->getOnlineLocationKeys();

        $cityResults = City::query()
            ->with('state')
            ->where('name', 'LIKE', $query.'%')
            ->whereIn('id', $onlineCityIds)
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
            ->limit(40)
            ->get()
            ->toArray();

        // Filter postcode rows to only those with at least one online profile
        $suburbResults = array_values(array_filter(
            $suburbResults,
            fn (array $row) => isset($onlineSuburbKeys[strtolower(trim($row['suburb'])).', '.strtolower($row['state'])])
        ));

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

    /**
     * Returns the city IDs and suburb+state keys that have at least one currently online profile.
     *
     * @return array{0: array<int>, 1: array<string, true>}
     */
    private function getOnlineLocationKeys(): array
    {
        $profiles = ProviderProfile::query()
            ->select(['city_id', 'suburb'])
            ->where('profile_status', 'approved')
            ->where('is_blocked', false)
            ->whereNull('deleted_at')
            ->whereDoesntHave('hideShowProfile', fn ($q) => $q->where('status', 'hide'))
            ->where(function ($q): void {
                $q->whereHas('onlineUser', fn ($q) => $q->where('status', 'online'))
                    ->orWhere(fn ($legacy) => $legacy
                        ->whereDoesntHave('onlineUser')
                        ->whereHas('user.onlineUser', fn ($q) => $q
                            ->whereNull('provider_profile_id')
                            ->where('status', 'online')));
            })
            ->get();

        // Collect city IDs from profiles that use the city_id relationship
        $onlineCityIds = $profiles
            ->whereNotNull('city_id')
            ->pluck('city_id')
            ->unique()
            ->filter()
            ->values()
            ->all();

        // Build suburb+state keys from profiles whose location is stored as free text
        // e.g. "Sydney, NSW 2000" -> key "sydney, nsw"
        $onlineSuburbKeys = [];

        foreach ($profiles->whereNotNull('suburb') as $profile) {
            $suburb = strtolower(trim((string) $profile->suburb));

            if ($suburb === '') {
                continue;
            }

            // Strip trailing postcode digits so "sydney, nsw 2000" becomes "sydney, nsw"
            $suburb = rtrim(preg_replace('/\s+\d{4}\s*$/', '', $suburb));

            if ($suburb !== '') {
                $onlineSuburbKeys[$suburb] = true;
            }
        }

        // Also add suburb+state keys derived from city_id-based profiles so that
        // postcode results match even when the profile uses city_id rather than suburb text.
        if (! empty($onlineCityIds)) {
            City::with('state')
                ->whereIn('id', $onlineCityIds)
                ->get(['id', 'name', 'state_id'])
                ->each(function (City $city) use (&$onlineSuburbKeys): void {
                    $stateName = $city->state?->name ?? '';
                    $stateAbbr = self::STATE_ABBREVIATIONS[$stateName] ?? strtoupper($stateName);
                    $key = strtolower(trim($city->name)).', '.strtolower($stateAbbr);
                    $onlineSuburbKeys[$key] = true;
                });
        }

        return [$onlineCityIds, $onlineSuburbKeys];
    }
}
