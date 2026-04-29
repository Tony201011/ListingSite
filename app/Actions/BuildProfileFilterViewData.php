<?php

namespace App\Actions;

use App\Concerns\ResolvesProfileCategoryIds;
use App\Models\Category;
use App\Models\Postcode;
use App\Models\ProfileView;
use App\Models\ProviderProfile;
use App\Models\SiteSetting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Scout\Builder as ScoutBuilder;

class BuildProfileFilterViewData
{
    use ResolvesProfileCategoryIds;

    private const DEFAULT_PROFILES_PER_PAGE = 12;

    private const DEFAULT_MIN_AGE = 18;

    private const DEFAULT_MAX_AGE = 40;

    private const DEFAULT_MIN_PRICE = 150;

    private const DEFAULT_MAX_PRICE = 400;

    private const DEFAULT_MAX_DISTANCE = 500;

    private const SLUG_TO_COLUMN = [
        'hair-color' => 'hair_color_id',
        'hair-length' => 'hair_length_id',
        'ethnicity' => 'ethnicity_id',
        'body-type' => 'body_type_id',
        'bust-size' => 'bust_size_id',
        'your-length' => 'your_length_id',
    ];

    private const SLUG_TO_JSON_COLUMN = [
        'primary-identity' => 'primary_identity',
        'attributes' => 'attributes',
        'services-style' => 'services_style',
        'services-you-provide' => 'services_provided',
    ];

    private const SLUG_TO_STRING_COLUMN = [
        'availability' => 'availability',
        'contact-method' => 'contact_method',
        'phone-contact-preferences' => 'phone_contact_preference',
        'time-waster-shield' => 'time_waster_shield',
    ];

    public function execute(array $validated): array
    {
        $filterSlugs = [
            'hair-color',
            'hair-length',
            'ethnicity',
            'body-type',
            'bust-size',
            'your-length',
            'primary-identity',
            'attributes',
            'services-style',
            'services-you-provide',
            'availability',
            'contact-method',
            'phone-contact-preferences',
            'time-waster-shield',
        ];

        $parents = Category::query()
            ->whereIn('slug', $filterSlugs)
            ->where('website_type', 'adult')
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'name', 'slug']);

        $childrenByParent = Category::query()
            ->whereIn('parent_id', $parents->pluck('id'))
            ->where('website_type', 'adult')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'name', 'parent_id'])
            ->groupBy('parent_id');

        $filterGroups = $parents
            ->sortBy(fn ($parent) => array_search($parent->slug, $filterSlugs, true))
            ->values()
            ->map(function ($parent) use ($childrenByParent) {
                return [
                    'slug' => $parent->slug,
                    'label' => $parent->name,
                    'options' => ($childrenByParent->get($parent->id) ?? collect())
                        ->map(fn ($child) => [
                            'id' => $child->id,
                            'name' => $child->name,
                        ])
                        ->values()
                        ->all(),
                ];
            })
            ->all();

        $allFilterCategories = collect($filterGroups)
            ->flatMap(fn ($group) => $group['options'])
            ->values()
            ->all();

        $selectedCategoryIds = collect($validated['categories'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        $minAge = (int) ($validated['min_age'] ?? self::DEFAULT_MIN_AGE);
        $maxAge = (int) ($validated['max_age'] ?? self::DEFAULT_MAX_AGE);
        $minPrice = (int) ($validated['min_price'] ?? self::DEFAULT_MIN_PRICE);
        $maxPrice = (int) ($validated['max_price'] ?? self::DEFAULT_MAX_PRICE);

        if ($minAge > $maxAge) {
            [$minAge, $maxAge] = [$maxAge, $minAge];
        }

        if ($minPrice > $maxPrice) {
            [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
        }

        $locationQuery = trim((string) ($validated['location'] ?? ''));
        $girlsMode = (string) ($validated['girls'] ?? 'all');
        $locationStateQuery = trim((string) ($validated['location_state'] ?? ''));
        $escortNameQuery = trim((string) ($validated['escort_name'] ?? ''));

        $setting = SiteSetting::query()->first(['max_search_distance', 'distance_search_enabled']);
        $distanceSearchEnabled = (bool) ($setting?->distance_search_enabled ?? true);

        $maxSearchDistance = (int) ($setting?->max_search_distance ?? self::DEFAULT_MAX_DISTANCE);
        if ($maxSearchDistance < 1) {
            $maxSearchDistance = self::DEFAULT_MAX_DISTANCE;
        }

        $hasExplicitDistance = isset($validated['distance']) && $validated['distance'] !== '';
        $distanceFilter = null;

        if ($distanceSearchEnabled && $hasExplicitDistance) {
            $requestedDistance = (int) $validated['distance'];
            $distanceFilter = min(max(1, $requestedDistance), $maxSearchDistance);
        }

        $rawUserLat = isset($validated['user_lat']) ? (float) $validated['user_lat'] : null;
        $rawUserLng = isset($validated['user_lng']) ? (float) $validated['user_lng'] : null;

        $resolvedLocation = $this->resolveExactLocation($locationQuery, $locationStateQuery);

        $geocodedLat = null;
        $geocodedLng = null;

        if ($resolvedLocation !== null) {
            $locationCoordinates = $this->resolveLocationCoordinates(
                $resolvedLocation['suburb'],
                $resolvedLocation['state']
            );

            if ($locationCoordinates !== null) {
                $geocodedLat = $locationCoordinates['latitude'];
                $geocodedLng = $locationCoordinates['longitude'];
            }
        }

        // Prefer geocoded coordinates over raw user GPS for the search centre.
        $userLat = $geocodedLat ?? $rawUserLat;
        $userLng = $geocodedLng ?? $rawUserLng;

        $categoryToParentSlug = $this->buildCategoryToParentSlugMap($parents, $childrenByParent);

        $categoryNameById = collect($allFilterCategories)
            ->pluck('name', 'id')
            ->all();

        $profiles = $this->queryProfiles(
            $locationQuery,
            $locationStateQuery,
            $minAge,
            $maxAge,
            $minPrice,
            $maxPrice,
            $selectedCategoryIds,
            $categoryToParentSlug,
            $categoryNameById,
            $userLat,
            $userLng,
            $distanceFilter,
            $girlsMode,
            $escortNameQuery,
        );

        $allFilterCategoriesCollection = collect($allFilterCategories);

        $selectedCategoryItems = $allFilterCategoriesCollection
            ->whereIn('id', $selectedCategoryIds)
            ->values();

        $hasAgeFilter = $minAge !== self::DEFAULT_MIN_AGE || $maxAge !== self::DEFAULT_MAX_AGE;
        $hasPriceFilter = $minPrice !== self::DEFAULT_MIN_PRICE || $maxPrice !== self::DEFAULT_MAX_PRICE;
        $hasDistanceFilter = $distanceFilter !== null;

        return compact(
            'filterGroups',
            'allFilterCategories',
            'selectedCategoryIds',
            'selectedCategoryItems',
            'minAge',
            'maxAge',
            'minPrice',
            'maxPrice',
            'locationQuery',
            'girlsMode',
            'profiles',
            'hasAgeFilter',
            'hasPriceFilter',
            'maxSearchDistance',
            'distanceFilter',
            'hasDistanceFilter',
            'distanceSearchEnabled',
            'userLat',
            'userLng',
            'escortNameQuery',
        );
    }

    private function resolveProfilesPerPage(): int
    {
        $value = (int) cache()->remember(
            'site_setting.home_page_records',
            now()->addHour(),
            fn () => SiteSetting::query()->value('home_page_records') ?? self::DEFAULT_PROFILES_PER_PAGE
        );

        return $value >= 1 ? $value : self::DEFAULT_PROFILES_PER_PAGE;
    }

    private function buildCategoryToParentSlugMap(Collection $parents, Collection $childrenByParent): array
    {
        $map = [];

        foreach ($parents as $parent) {
            $children = $childrenByParent->get($parent->id) ?? collect();
            foreach ($children as $child) {
                $map[$child->id] = $parent->slug;
            }
        }

        return $map;
    }

    private function queryProfiles(
        string $locationQuery,
        string $locationStateQuery,
        int $minAge,
        int $maxAge,
        int $minPrice,
        int $maxPrice,
        array $selectedCategoryIds,
        array $categoryToParentSlug,
        array $categoryNameById,
        ?float $userLat = null,
        ?float $userLng = null,
        ?int $distanceFilter = null,
        string $girlsMode = 'all',
        string $escortNameQuery = '',
    ): LengthAwarePaginator {
        $hasLocationQuery = $locationQuery !== '';
        $exactLocation = $this->resolveExactLocation($locationQuery, $locationStateQuery);
        $distanceSearchActive = $distanceFilter !== null && $userLat !== null && $userLng !== null;

        $scoutMatchedIds = null;
        if ($hasLocationQuery && $exactLocation === null) {
            $scoutMatchedIds = $this->resolveScoutIds($locationQuery);
        }

        $query = ProviderProfile::query()
            ->whereNull('provider_profiles.deleted_at')
            ->where('provider_profiles.profile_status', 'approved')
            ->whereHas('user')
            ->with([
                'user.profileImages' => fn ($q) => $q->where('is_primary', true),
                'user.rates',
                'user.onlineUser',
                'city',
            ]);

        if (! $distanceSearchActive) {
            if ($exactLocation !== null) {
                $query->where(function ($q) use ($exactLocation) {
                    $this->applyExactLocationFilter($q, $exactLocation);
                });
            } elseif ($hasLocationQuery) {
                if ($scoutMatchedIds !== null && $scoutMatchedIds->isNotEmpty()) {
                    $query->whereIn('provider_profiles.id', $scoutMatchedIds);
                } else {
                    $query->where(function ($q) use ($locationQuery) {
                        $q->whereHas('city', fn ($cityQ) => $cityQ->where('name', 'like', '%'.$locationQuery.'%'))
                            ->orWhereHas('user', fn ($userQ) => $userQ->where('suburb', 'like', '%'.$locationQuery.'%'));
                    });
                }
            }
        }

        if ($minAge > self::DEFAULT_MIN_AGE || $maxAge < self::DEFAULT_MAX_AGE) {
            $query->whereBetween('provider_profiles.age', [$minAge, $maxAge]);
        }

        if ($escortNameQuery !== '') {
            $query->where('provider_profiles.name', 'like', '%'.$escortNameQuery.'%');
        }

        if ($minPrice !== self::DEFAULT_MIN_PRICE || $maxPrice !== self::DEFAULT_MAX_PRICE) {
            $query->whereHas('user.rates', function ($q) use ($minPrice, $maxPrice): void {
                $q->whereRaw(
                    "CASE
                        WHEN incall IS NOT NULL AND TRIM(incall) != ''
                            THEN CAST(REGEXP_REPLACE(incall, '[^0-9]', '') AS UNSIGNED)
                        WHEN outcall IS NOT NULL AND TRIM(outcall) != ''
                            THEN CAST(REGEXP_REPLACE(outcall, '[^0-9]', '') AS UNSIGNED)
                        ELSE 0
                    END BETWEEN ? AND ?",
                    [$minPrice, $maxPrice]
                );
            });
        }

        if (! empty($selectedCategoryIds)) {
            $selectedBySlug = [];

            foreach ($selectedCategoryIds as $categoryId) {
                $slug = $categoryToParentSlug[$categoryId] ?? null;
                if ($slug !== null) {
                    $selectedBySlug[$slug][] = $categoryId;
                }
            }

            if (! empty($selectedBySlug)) {
                $query->where(function ($q) use ($selectedBySlug, $categoryNameById): void {
                    foreach ($selectedBySlug as $slug => $ids) {
                        $column = self::SLUG_TO_COLUMN[$slug] ?? null;
                        if ($column !== null) {
                            $q->whereIn($column, $ids);

                            continue;
                        }

                        $jsonColumn = self::SLUG_TO_JSON_COLUMN[$slug] ?? null;
                        if ($jsonColumn !== null) {
                            $names = array_values(array_filter(
                                array_map(fn ($id) => $categoryNameById[$id] ?? null, $ids)
                            ));

                            if (! empty($names)) {
                                $q->where(function ($inner) use ($jsonColumn, $names): void {
                                    foreach ($names as $name) {
                                        $inner->orWhereJsonContains($jsonColumn, $name);
                                    }
                                });
                            }

                            continue;
                        }

                        $stringColumn = self::SLUG_TO_STRING_COLUMN[$slug] ?? null;
                        if ($stringColumn !== null) {
                            $names = array_values(array_filter(
                                array_map(fn ($id) => $categoryNameById[$id] ?? null, $ids)
                            ));

                            if (! empty($names)) {
                                $q->whereIn($stringColumn, $names);
                            }
                        }
                    }
                });
            }
        }

        $distanceMap = [];
        $distanceOrderingApplied = false;

        if ($distanceSearchActive) {
            $distanceRows = $this->findNearbyProfileDistances($userLat, $userLng, $distanceFilter);

            if (empty($distanceRows)) {
                $query->whereRaw('1 = 0');
            } else {
                $distanceMap = collect($distanceRows)->pluck('distance_km', 'provider_profile_id')->all();
                $nearbyIds = array_keys($distanceMap);

                $query->whereIn('provider_profiles.id', $nearbyIds);

                $distanceOrderSql = collect($distanceMap)
                    ->map(function ($distance, $id) {
                        $id = (int) $id;
                        $distance = (float) $distance;

                        return "WHEN {$id} THEN {$distance}";
                    })
                    ->implode(' ');

                $query->orderByRaw("
                    CASE provider_profiles.id
                        {$distanceOrderSql}
                        ELSE 999999
                    END ASC
                ");

                $distanceOrderingApplied = true;
            }
        }

        switch ($girlsMode) {
            case 'popular':
                $query->addSelect([
                    'popularity_score' => ProfileView::query()
                        ->selectRaw('count(*)')
                        ->whereColumn('profile_views.user_id', 'provider_profiles.user_id'),
                ]);

                if (! $distanceOrderingApplied) {
                    $query->orderByDesc('popularity_score')
                        ->orderByDesc('provider_profiles.is_featured')
                        ->orderByDesc('provider_profiles.created_at');
                }
                break;

            case 'new':
                if (! $distanceOrderingApplied) {
                    $query->orderByDesc('provider_profiles.created_at')
                        ->orderByDesc('provider_profiles.is_featured');
                }
                break;

            default:
                if (! $distanceOrderingApplied) {
                    $query->orderByDesc('provider_profiles.is_featured')
                        ->orderByDesc('provider_profiles.created_at');
                }
                break;
        }

        $appendParams = array_filter([
            'location' => $locationQuery ?: null,
            'min_age' => $minAge !== self::DEFAULT_MIN_AGE ? $minAge : null,
            'max_age' => $maxAge !== self::DEFAULT_MAX_AGE ? $maxAge : null,
            'min_price' => $minPrice !== self::DEFAULT_MIN_PRICE ? $minPrice : null,
            'max_price' => $maxPrice !== self::DEFAULT_MAX_PRICE ? $maxPrice : null,
            'distance' => $distanceFilter,
        ]);

        $appendParams['girls'] = $girlsMode;

        foreach ($selectedCategoryIds as $categoryId) {
            $appendParams['categories'][] = $categoryId;
        }

        $paginator = $query
            ->paginate($this->resolveProfilesPerPage())
            ->appends($appendParams);

        $serviceIds = $paginator->getCollection()
            ->flatMap(fn (ProviderProfile $p) => array_filter((array) ($p->services_provided ?? []), 'is_numeric'))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();

        $categoryNames = $serviceIds
            ? Category::query()->whereIn('id', $serviceIds)->pluck('name', 'id')
            : collect();

        $paginator->getCollection()->transform(function (ProviderProfile $profile) use ($categoryNames, $distanceMap) {
            if (array_key_exists($profile->id, $distanceMap)) {
                $profile->distance_km = $distanceMap[$profile->id];
            }

            return $this->transformProfile($profile, $categoryNames);
        });

        return $paginator;
    }

    private function findNearbyProfileDistances(float $searchLat, float $searchLng, int $distanceKm): array
    {
        $latDelta = $distanceKm / 111.0;
        $lngDivisor = max(cos(deg2rad($searchLat)), 0.01);
        $lngDelta = $distanceKm / (111.0 * $lngDivisor);

        $minLat = $searchLat - $latDelta;
        $maxLat = $searchLat + $latDelta;
        $minLng = $searchLng - $lngDelta;
        $maxLng = $searchLng + $lngDelta;

        $stateCaseSql = "
            CASE states.name
                WHEN 'Australian Capital Territory' THEN 'ACT'
                WHEN 'New South Wales' THEN 'NSW'
                WHEN 'Victoria' THEN 'VIC'
                WHEN 'Queensland' THEN 'QLD'
                WHEN 'Western Australia' THEN 'WA'
                WHEN 'South Australia' THEN 'SA'
                WHEN 'Tasmania' THEN 'TAS'
                WHEN 'Northern Territory' THEN 'NT'
                ELSE NULL
            END
        ";

        $distanceSql = '(6371 * acos(
            LEAST(1, GREATEST(-1,
                cos(radians(?)) *
                cos(radians(profile_postcodes.latitude)) *
                cos(radians(profile_postcodes.longitude) - radians(?)) +
                sin(radians(?)) *
                sin(radians(profile_postcodes.latitude))
            ))
        ))';

        return DB::table('provider_profiles')
            ->join('users', 'users.id', '=', 'provider_profiles.user_id')
            ->leftJoin('states', 'states.id', '=', 'provider_profiles.state_id')
            ->join('postcodes as profile_postcodes', function ($join) use ($stateCaseSql) {
                $join->whereRaw('UPPER(TRIM(profile_postcodes.suburb)) = UPPER(TRIM(SUBSTRING_INDEX(users.suburb, ",", 1)))')
                    ->whereRaw("
                        UPPER(TRIM(profile_postcodes.state)) = UPPER(TRIM(
                            COALESCE(
                                NULLIF(
                                    CASE
                                        WHEN users.suburb LIKE '%,%' THEN SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(users.suburb, ',', -1)), ' ', 1)
                                        ELSE NULL
                                    END,
                                    ''
                                ),
                                {$stateCaseSql}
                            )
                        ))
                    ");
            })
            ->whereNull('provider_profiles.deleted_at')
            ->where('provider_profiles.profile_status', 'approved')
            ->whereBetween('profile_postcodes.latitude', [$minLat, $maxLat])
            ->whereBetween('profile_postcodes.longitude', [$minLng, $maxLng])
            ->select('provider_profiles.id as provider_profile_id')
            ->selectRaw("{$distanceSql} as distance_km", [$searchLat, $searchLng, $searchLat])
            ->having('distance_km', '<=', $distanceKm)
            ->orderBy('distance_km')
            ->get()
            ->map(fn ($row) => [
                'provider_profile_id' => (int) $row->provider_profile_id,
                'distance_km' => round((float) $row->distance_km, 1),
            ])
            ->all();
    }

    private function resolveScoutIds(string $locationQuery): ?Collection
    {
        try {
            $searchTerm = trim($locationQuery);

            /** @var ScoutBuilder $scoutQuery */
            $scoutQuery = ProviderProfile::search($searchTerm)
                ->where('profile_status', 'approved');

            $results = $scoutQuery->take(1000)->keys();

            return collect($results)->map(fn ($id) => (int) $id);
        } catch (\Throwable) {
            return null;
        }
    }

    private function resolveExactLocation(?string $locationQuery, ?string $locationStateQuery): ?array
    {
        $locationQuery = trim((string) $locationQuery);
        $locationStateQuery = trim((string) $locationStateQuery);

        if ($locationQuery === '') {
            return null;
        }

        if (str_contains($locationQuery, ',')) {
            [$suburb, $state] = array_map(
                fn ($value) => trim($value),
                explode(',', $locationQuery, 2)
            );

            if ($suburb === '' || $state === '') {
                return null;
            }

            return [
                'suburb' => $suburb,
                'state' => $this->normalizeStateAbbreviation($state) ?? strtoupper($state),
            ];
        }

        if ($locationStateQuery !== '') {
            return [
                'suburb' => $locationQuery,
                'state' => $this->normalizeStateAbbreviation($locationStateQuery) ?? strtoupper($locationStateQuery),
            ];
        }

        $matchedStates = Postcode::query()
            ->whereRaw('UPPER(TRIM(suburb)) = ?', [mb_strtoupper($locationQuery)])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->pluck('state')
            ->map(fn ($state) => strtoupper(trim((string) $state)))
            ->unique()
            ->values();

        if ($matchedStates->count() === 1) {
            $state = $matchedStates->first();

            return [
                'suburb' => $locationQuery,
                'state' => $this->normalizeStateAbbreviation($state) ?? $state,
            ];
        }

        return null;
    }

    private function resolveLocationCoordinates(string $suburb, string $state): ?array
    {
        $postcode = Postcode::query()
            ->whereRaw('UPPER(TRIM(suburb)) = ?', [mb_strtoupper(trim($suburb))])
            ->whereRaw('UPPER(TRIM(state)) = ?', [mb_strtoupper(trim($state))])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('postcode')
            ->first(['latitude', 'longitude']);

        if ($postcode === null) {
            return null;
        }

        return [
            'latitude' => (float) $postcode->latitude,
            'longitude' => (float) $postcode->longitude,
        ];
    }

    private function applyExactLocationFilter(Builder $query, array $exactLocation): void
    {
        $suburb = trim((string) $exactLocation['suburb']);
        $state = strtoupper(trim((string) $exactLocation['state']));
        $fullStateName = $this->resolveStateName($state);

        $query->where(function (Builder $outer) use ($suburb, $state, $fullStateName): void {
            $outer->whereHas('city', function (Builder $cityQuery) use ($suburb, $fullStateName): void {
                $cityQuery->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($suburb)])
                    ->whereHas('state', function (Builder $stateQuery) use ($fullStateName): void {
                        $stateQuery->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($fullStateName)]);
                    });
            })->orWhereHas('user', function (Builder $userQuery) use ($suburb, $state): void {
                $userQuery->whereRaw(
                    'LOWER(TRIM(suburb)) LIKE ?',
                    [mb_strtolower($suburb.', '.$state).'%']
                );
            });
        });
    }

    private function resolveStateName(string $state): string
    {
        $map = [
            'ACT' => 'Australian Capital Territory',
            'NSW' => 'New South Wales',
            'VIC' => 'Victoria',
            'QLD' => 'Queensland',
            'WA' => 'Western Australia',
            'SA' => 'South Australia',
            'TAS' => 'Tasmania',
            'NT' => 'Northern Territory',
        ];

        return $map[strtoupper(trim($state))] ?? $state;
    }

    private function normalizeStateAbbreviation(string $state): ?string
    {
        $map = [
            'ACT' => 'ACT',
            'NSW' => 'NSW',
            'VIC' => 'VIC',
            'QLD' => 'QLD',
            'WA' => 'WA',
            'SA' => 'SA',
            'TAS' => 'TAS',
            'NT' => 'NT',
            'AUSTRALIAN CAPITAL TERRITORY' => 'ACT',
            'NEW SOUTH WALES' => 'NSW',
            'VICTORIA' => 'VIC',
            'QUEENSLAND' => 'QLD',
            'WESTERN AUSTRALIA' => 'WA',
            'SOUTH AUSTRALIA' => 'SA',
            'TASMANIA' => 'TAS',
            'NORTHERN TERRITORY' => 'NT',
        ];

        $uppercase = strtoupper(trim($state));

        return $map[$uppercase] ?? null;
    }

    private function transformProfile(ProviderProfile $profile, Collection $categoryNames): array
    {
        $primaryImage = $profile->user?->profileImages?->first();
        $imageUrl = $primaryImage?->thumbnail_url ?? $primaryImage?->image_url ?? null;

        $firstRate = $profile->user?->rates?->first();
        $rateDisplay = $this->formatRate($firstRate);

        $services = $this->resolveIds(
            array_values(array_filter((array) ($profile->services_provided ?? []))),
            $categoryNames
        );

        $isOnline = $profile->user?->onlineUser?->isCurrentlyOnline() ?? false;

        return [
            'name' => $profile->name,
            'age' => $profile->age,
            'rate' => $rateDisplay,
            'rate_numeric' => $this->extractNumericRate($firstRate),
            'in_call' => trim((string) ($firstRate?->incall ?? '')),
            'out_call' => trim((string) ($firstRate?->outcall ?? '')),
            'city' => $profile->city?->name ?? '',
            'suburb' => $profile->user?->suburb,
            'distance_km' => isset($profile->distance_km) ? round((float) $profile->distance_km, 1) : null,
            'height' => '',
            'service_1' => $services[0] ?? '',
            'service_2' => $services[1] ?? '',
            'date' => $profile->created_at->format('d/m/Y'),
            'description' => $profile->description ?? '',
            'active' => $isOnline,
            'verified' => $profile->is_verified,
            'image' => $imageUrl ?? '',
            'slug' => $profile->slug,
        ];
    }

    private function formatRate(mixed $rate): string
    {
        if ($rate === null) {
            return 'Contact for rate';
        }

        $incall = trim((string) ($rate->incall ?? ''));
        if ($incall !== '') {
            return $incall;
        }

        $outcall = trim((string) ($rate->outcall ?? ''));

        return $outcall !== '' ? $outcall : 'Contact for rate';
    }

    private function extractNumericRate(mixed $rate): int
    {
        if ($rate === null) {
            return 0;
        }

        $value = (string) ($rate->incall ?? $rate->outcall ?? '');
        $digits = preg_replace('/[^\d]/', '', $value);

        return $digits !== '' ? (int) $digits : 0;
    }
}
