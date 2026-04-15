<?php

namespace App\Actions;

use App\Concerns\ResolvesProfileCategoryIds;
use App\Models\Category;
use App\Models\ProviderProfile;
use App\Models\SiteSetting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Laravel\Scout\Builder as ScoutBuilder;

class BuildProfileFilterViewData
{
    use ResolvesProfileCategoryIds;

    private const DEFAULT_PROFILES_PER_PAGE = 12;

    private const DEFAULT_MIN_AGE = 18;

    private const DEFAULT_MAX_AGE = 40;

    private const DEFAULT_MIN_PRICE = 150;

    private const DEFAULT_MAX_PRICE = 400;

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

    private const DEFAULT_MAX_DISTANCE = 500;

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

        $locationQuery = (string) ($validated['location'] ?? '');
        $escortNameQuery = (string) ($validated['escort_name'] ?? '');

        $userLat = isset($validated['user_lat']) && $validated['user_lat'] !== '' ? (float) $validated['user_lat'] : null;
        $userLng = isset($validated['user_lng']) && $validated['user_lng'] !== '' ? (float) $validated['user_lng'] : null;

        $maxSearchDistance = (int) (SiteSetting::query()->value('max_search_distance') ?? self::DEFAULT_MAX_DISTANCE);
        if ($maxSearchDistance < 1) {
            $maxSearchDistance = self::DEFAULT_MAX_DISTANCE;
        }

        $distanceFilter = null;
        if ($userLat !== null && $userLng !== null) {
            $requestedDistance = isset($validated['distance']) && $validated['distance'] !== '' ? (int) $validated['distance'] : $maxSearchDistance;
            $distanceFilter = min(max(1, $requestedDistance), $maxSearchDistance);
        }

        $categoryToParentSlug = $this->buildCategoryToParentSlugMap($parents, $childrenByParent);

        $categoryNameById = collect($allFilterCategories)
            ->pluck('name', 'id')
            ->all();

        $profiles = $this->queryProfiles(
            $locationQuery,
            $escortNameQuery,
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
            'escortNameQuery',
            'profiles',
            'hasAgeFilter',
            'hasPriceFilter',
            'maxSearchDistance',
            'distanceFilter',
            'hasDistanceFilter',
            'userLat',
            'userLng',
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
        string $escortNameQuery,
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
    ): LengthAwarePaginator {
        $hasLocationQuery = $locationQuery !== '';

        // When a location query is present, use Typesense via Laravel Scout to resolve
        // matching profile IDs, then constrain the Eloquent query to those IDs.
        // This provides fast, typo-tolerant full-text search while keeping all
        // relational filters (age, price, categories) on the Eloquent side.
        // NOTE: escort_name is intentionally excluded from Scout to avoid fuzzy/typo-tolerant
        // matching, which would return unrelated names (e.g. searching "2620" returning "2621").
        $scoutMatchedIds = null;
        if ($hasLocationQuery) {
            $scoutMatchedIds = $this->resolveScoutIds($locationQuery);
        }

        $query = ProviderProfile::query()
            ->whereNull('deleted_at')
            ->where('profile_status', 'approved')
            ->whereHas('user')
            ->with([
                'user.profileImages' => fn ($q) => $q->where('is_primary', true),
                'user.rates',
                'user.onlineUser',
                'city',
            ]);

        if ($scoutMatchedIds !== null) {
            // If Scout returned IDs, constrain the query to those profiles.
            if ($scoutMatchedIds->isEmpty()) {
                // No Scout results – force an empty result set.
                $query->whereRaw('0 = 1');
            } else {
                $query->whereIn('id', $scoutMatchedIds);
            }
        } elseif ($hasLocationQuery) {
            // Scout is not configured or unavailable – fall back to LIKE queries for location.
            $query->where(function ($q) use ($locationQuery) {
                $q->whereHas('city', fn ($q) => $q->where('name', 'like', '%'.$locationQuery.'%'))
                    ->orWhereHas('user', fn ($q) => $q->where('suburb', 'like', '%'.$locationQuery.'%'));
            });
        }

        // Always apply escort name as an exact LIKE filter to avoid fuzzy matching.
        if ($escortNameQuery !== '') {
            $query->where('name', 'like', '%'.$escortNameQuery.'%');
        }

        if ($minAge > 18 || $maxAge < 40) {
            $query->whereBetween('age', [$minAge, $maxAge]);
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

        if ($distanceFilter !== null && $userLat !== null && $userLng !== null) {
            // Haversine formula – filter profiles whose lat/lng is within $distanceFilter km.
            // Profiles without coordinates are excluded when a distance filter is active.
            $query->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->whereRaw(
                    '(6371 * acos(
                        cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) +
                        sin(radians(?)) * sin(radians(latitude))
                    )) <= ?',
                    [$userLat, $userLng, $userLat, $distanceFilter]
                );
        }

        $appendParams = array_filter([
            'location' => $locationQuery ?: null,
            'escort_name' => $escortNameQuery ?: null,
            'min_age' => $minAge !== self::DEFAULT_MIN_AGE ? $minAge : null,
            'max_age' => $maxAge !== self::DEFAULT_MAX_AGE ? $maxAge : null,
            'min_price' => $minPrice !== self::DEFAULT_MIN_PRICE ? $minPrice : null,
            'max_price' => $maxPrice !== self::DEFAULT_MAX_PRICE ? $maxPrice : null,
            'user_lat' => $userLat,
            'user_lng' => $userLng,
            'distance' => $distanceFilter,
        ]);

        foreach ($selectedCategoryIds as $categoryId) {
            $appendParams['categories'][] = $categoryId;
        }

        $paginator = $query
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
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

        $paginator->getCollection()->transform(fn (ProviderProfile $profile) => $this->transformProfile($profile, $categoryNames));

        return $paginator;
    }

    /**
     * Use Laravel Scout (Typesense) to resolve profile IDs matching the text queries.
     * Returns null when Scout is unavailable or throws an exception, signalling that
     * the caller should fall back to Eloquent LIKE queries.
     *
     * @return \Illuminate\Support\Collection<int>|null
     */
    private function resolveScoutIds(string $locationQuery): ?Collection
    {
        try {
            $searchTerm = trim($locationQuery);

            /** @var ScoutBuilder $scoutQuery */
            $scoutQuery = ProviderProfile::search($searchTerm)
                ->where('profile_status', 'approved');

            // Retrieve up to 1000 matching IDs so that downstream Eloquent
            // pagination can work correctly against the full filtered set.
            $results = $scoutQuery->take(1000)->keys();

            return collect($results)->map(fn ($id) => (int) $id);
        } catch (\Throwable) {
            // Typesense is unreachable or not yet indexed – fall back gracefully.
            return null;
        }
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
            'city' => $profile->city?->name ?? '',
            'suburb' => $profile->user?->suburb ?? '',
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
