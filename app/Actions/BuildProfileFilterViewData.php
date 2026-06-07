<?php

namespace App\Actions;

use App\Concerns\ResolvesProfileCategoryIds;
use App\Models\Category;
use App\Models\Postcode;
use App\Models\ProfileView;
use App\Models\ProviderProfile;
use App\Models\SiteSetting;
use App\Services\ListingPaginationUrlService;
use App\Support\SeoFriendlyLengthAwarePaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Scout\Builder as ScoutBuilder;

class BuildProfileFilterViewData
{
    use ResolvesProfileCategoryIds;

    public function __construct(
        private readonly ListingPaginationUrlService $listingPaginationUrlService,
    ) {}

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

    public function execute(array $validated, bool $syncWithAdminOnlineListing = false, bool $advancedSearch = false): array
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
        // Featured sections are hidden when an escort_name filter is active, so do not apply
        // the featured exclusion filter either (otherwise local-banner profiles would be absent
        // from both the hidden featured strip AND the main results).
        $localFeaturedStateName = $escortNameQuery === ''
            ? $this->resolveLocalFeaturedStateName($locationQuery, $locationStateQuery)
            : null;

        // Home banner profiles (national) are shown in their own strip on local pages too.
        // When a location filter is active and the home banner section is visible, exclude those
        // profiles from the main listing so they do not appear in both places.
        $excludeHomeBannerProfiles = $escortNameQuery === '' && ($locationQuery !== '' || $locationStateQuery !== '');

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
            $localFeaturedStateName,
            $syncWithAdminOnlineListing,
            $validated,
            $advancedSearch,
            $excludeHomeBannerProfiles,
        );
        $onlineCount = $profiles->total();

        $allFilterCategoriesCollection = collect($allFilterCategories);

        $selectedCategoryItems = $allFilterCategoriesCollection
            ->whereIn('id', $selectedCategoryIds)
            ->values();

        $hasAgeFilter = $minAge !== self::DEFAULT_MIN_AGE || $maxAge !== self::DEFAULT_MAX_AGE;
        $hasPriceFilter = $minPrice !== self::DEFAULT_MIN_PRICE || $maxPrice !== self::DEFAULT_MAX_PRICE;
        $hasDistanceFilter = $distanceFilter !== null;

        // Load home-banner profiles (national) — shown in dedicated banner section
        // Featured sections are hidden when an escort_name filter is active.
        $homeBannerProfiles = $escortNameQuery === ''
            ? $this->queryBannerProfiles('home_banner_expires_at')
            : collect();

        // Load local-banner profiles — shown when a state filter is active
        $localBannerProfiles = $escortNameQuery === '' && ($locationStateQuery !== '' || ($locationQuery !== '' && str_contains($locationQuery, ',')))
            ? $this->queryBannerProfiles('local_banner_expires_at', $locationStateQuery ?: null, $locationQuery, $resolvedLocation)
            : collect();

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
            'onlineCount',
            'homeBannerProfiles',
            'localBannerProfiles',
        );
    }

    /**
     * Return all currently active featured profiles grouped by tier,
     * for the public /featured listing page.
     */
    public function getFeaturedListingsData(): array
    {
        $homeBannerProfiles = $this->queryBannerProfiles('home_banner_expires_at');
        $homeFeaturedProfiles = $this->queryBannerProfiles('home_featured_expires_at');
        $localBannerProfiles = $this->queryBannerProfiles('local_banner_expires_at');
        $featuredProfiles = $this->queryBannerProfiles('featured_expires_at');

        return compact(
            'homeBannerProfiles',
            'homeFeaturedProfiles',
            'localBannerProfiles',
            'featuredProfiles',
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

    /**
     * Query profiles that hold a specific ad-tier placement.
     * Featured/banner sections show all profiles with an active paid placement
     * regardless of online status; only the main listing requires online status.
     */
    private function queryBannerProfiles(
        string $expiryColumn,
        ?string $locationStateQuery = null,
        ?string $locationQuery = null,
        ?array $exactLocation = null
    ): Collection {
        $query = ProviderProfile::query()
            ->whereNull('provider_profiles.deleted_at')
            ->where('provider_profiles.profile_status', 'approved')
            ->where('provider_profiles.is_blocked', false)
            ->whereHas('user')
            ->whereDoesntHave('hideShowProfile', fn ($q) => $q->where('status', 'hide'))
            ->whereNotNull("provider_profiles.{$expiryColumn}")
            ->where(function (Builder $q) use ($expiryColumn): void {
                $q->where("provider_profiles.{$expiryColumn}", '>', now())
                    ->orWhereDate("provider_profiles.{$expiryColumn}", today());
            })
            ->with([
                'profileImages' => fn ($q) => $q->orderByDesc('is_primary'),
                'photoVerification' => fn ($q) => $q->where('status', 'approved')->orderByDesc('submitted_at'),
                'rates',
                'onlineUser',
                'availableNow',
                'user',
                'user.onlineUser',
                'city',
                'state',
            ])
            ->orderByDesc("provider_profiles.{$expiryColumn}");

        // For local banner: restrict to the state being viewed
        if ($expiryColumn === 'local_banner_expires_at' && ($locationStateQuery !== null || $locationQuery !== null)) {
            if ($exactLocation !== null) {
                $this->applyExactLocationFilter($query, $exactLocation);
            }

            $state = $locationStateQuery ?: '';

            if ($state === '' && $locationQuery !== null && str_contains($locationQuery, ',')) {
                $parts = explode(',', $locationQuery, 2);
                $state = trim($parts[1] ?? '');
            }

            if ($state !== '' && $exactLocation === null) {
                $normalizedState = $this->normalizeStateAbbreviation($state) ?? strtoupper($state);
                $fullStateName = $this->resolveStateName($normalizedState);

                $query->where(function (Builder $q) use ($fullStateName, $normalizedState): void {
                    $q->whereHas('state', function (Builder $stateQuery) use ($fullStateName): void {
                        $stateQuery->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($fullStateName)]);
                    })->orWhereRaw(
                        'LOWER(TRIM(provider_profiles.suburb)) LIKE ?',
                        ['%, '.mb_strtolower($normalizedState).'%']
                    )->orWhereRaw(
                        'LOWER(TRIM(provider_profiles.suburb)) LIKE ?',
                        ['%, '.mb_strtolower($fullStateName).'%']
                    );
                });
            }
        }

        $profiles = $query->get();

        $serviceIds = $profiles
            ->flatMap(fn (ProviderProfile $p) => array_filter((array) ($p->services_provided ?? []), 'is_numeric'))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();

        $categoryNames = $serviceIds
            ? Category::query()->whereIn('id', $serviceIds)->pluck('name', 'id')
            : collect();

        return $profiles->map(fn (ProviderProfile $p) => $this->transformProfile($p, $categoryNames));
    }

    /**
     * Apply home-featured tier ordering so that profiles with an active
     * home_featured_expires_at appear before non-home-featured profiles.
     * Works with both MySQL (NOW()) and SQLite (parameterized datetime string).
     */
    private function applyHomeFeaturedOrdering(Builder $query): void
    {
        $query->orderByRaw(
            'CASE WHEN provider_profiles.home_featured_expires_at > ? THEN 1 ELSE 0 END DESC',
            [now()->toDateTimeString()]
        );
    }

    private function applyActiveOnlineProfileConstraint(Builder $query): void
    {
        $query->whereCurrentlyOnline();
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
        ?string $localFeaturedStateName = null,
        bool $syncWithAdminOnlineListing = false,
        array $validated = [],
        bool $advancedSearch = false,
        bool $excludeHomeBannerProfiles = false,
    ): LengthAwarePaginator {
        $hasLocationQuery = $locationQuery !== '';
        $exactLocation = $this->resolveExactLocation($locationQuery, $locationStateQuery);
        $distanceSearchActive = $distanceFilter !== null && $userLat !== null && $userLng !== null;

        $scoutMatchedIds = null;
        if ($hasLocationQuery && $exactLocation === null) {
            $scoutMatchedIds = $this->resolveScoutIds($locationQuery);
        }

        $query = ProviderProfile::query()
            ->withoutTrashed()
            ->with([
                'profileImages' => fn ($q) => $q->orderByDesc('is_primary'),
                'photoVerification' => fn ($q) => $q->where('status', 'approved')->orderByDesc('submitted_at'),
                'rates',
                'onlineUser',
                'availableNow',
                'user',
                'user.onlineUser',
                'city',
                'state',
            ]);

        if (! $syncWithAdminOnlineListing) {
            $query
                ->where('provider_profiles.profile_status', 'approved')
                ->where('provider_profiles.is_blocked', false)
                ->whereHas('user')
                ->whereDoesntHave('hideShowProfile', fn ($q) => $q->where('status', 'hide'));
        }

        $this->applyActiveOnlineProfileConstraint($query);

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
                            ->orWhere('provider_profiles.suburb', 'like', '%'.$locationQuery.'%');
                    });
                }
            }
        }

        if ($localFeaturedStateName !== null) {
            $query->where(function (Builder $q) use ($localFeaturedStateName): void {
                $q->whereNull('provider_profiles.local_banner_expires_at')
                    ->orWhere('provider_profiles.local_banner_expires_at', '<=', now())
                    ->orWhereDoesntHave('state', function (Builder $stateQuery) use ($localFeaturedStateName): void {
                        $stateQuery->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($localFeaturedStateName)]);
                    });
            });
        }

        // On local pages the home banner strip already displays these profiles nationally.
        // Exclude them from the main paginated listing to avoid showing them twice.
        if ($excludeHomeBannerProfiles) {
            $query->where(function (Builder $q): void {
                $q->whereNull('provider_profiles.home_banner_expires_at')
                    ->orWhere('provider_profiles.home_banner_expires_at', '<=', now());
            });
        }

        if ($minAge > self::DEFAULT_MIN_AGE || $maxAge < self::DEFAULT_MAX_AGE) {
            $query->whereBetween('provider_profiles.age', [$minAge, $maxAge]);
        }

        if ($escortNameQuery !== '') {
            $normalizedEscortNameQuery = preg_replace('/[\s-]+/', '', mb_strtolower($escortNameQuery)) ?? '';

            $query->where(function (Builder $nameQuery) use ($escortNameQuery, $normalizedEscortNameQuery): void {
                $nameQuery->whereRaw('LOWER(provider_profiles.name) LIKE ?', ['%'.mb_strtolower($escortNameQuery).'%']);

                if ($normalizedEscortNameQuery !== '') {
                    $nameQuery->orWhereRaw(
                        "LOWER(REPLACE(REPLACE(provider_profiles.name, '-', ''), ' ', '')) LIKE ?",
                        ['%'.$normalizedEscortNameQuery.'%']
                    );
                }
            });
        }

        if ($minPrice !== self::DEFAULT_MIN_PRICE || $maxPrice !== self::DEFAULT_MAX_PRICE) {
            $query->whereHas('rates', function ($q) use ($minPrice, $maxPrice): void {
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
                        ->whereColumn('profile_views.provider_profile_id', 'provider_profiles.id'),
                ]);

                if (! $distanceOrderingApplied) {
                    $query->orderByDesc('popularity_score');
                    $this->applyHomeFeaturedOrdering($query);
                    $query->orderByDesc('provider_profiles.is_featured')
                        ->orderByDesc('provider_profiles.created_at');
                }
                break;

            case 'new':
                if (! $distanceOrderingApplied) {
                    $query->orderByDesc('provider_profiles.created_at');
                    $this->applyHomeFeaturedOrdering($query);
                    $query->orderByDesc('provider_profiles.is_featured');
                }
                break;

            default:
                if (! $distanceOrderingApplied) {
                    $this->applyHomeFeaturedOrdering($query);
                    $query->orderByDesc('provider_profiles.is_featured')
                        ->orderByDesc('provider_profiles.created_at');
                }
                break;
        }

        $profilesPerPage = $this->resolveProfilesPerPage();
        $currentPage = max(1, (int) request()->route('page', request()->integer('page', 1)));
        $paginationContext = $this->listingPaginationUrlService->buildContext($validated, $advancedSearch);

        $paginator = $query
            ->paginate($profilesPerPage, ['*'], 'page', $currentPage);

        $paginator = SeoFriendlyLengthAwarePaginator::fromPaginator($paginator, $paginationContext['base_url'])
            ->appends($paginationContext['query']);

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
            ->leftJoin('states', 'states.id', '=', 'provider_profiles.state_id')
            ->leftJoin('hide_show_profiles', 'hide_show_profiles.provider_profile_id', '=', 'provider_profiles.id')
            ->join('postcodes as profile_postcodes', function ($join) use ($stateCaseSql) {
                $join->whereRaw('UPPER(TRIM(profile_postcodes.suburb)) = UPPER(TRIM(SUBSTRING_INDEX(provider_profiles.suburb, ",", 1)))')
                    ->whereRaw("
                        UPPER(TRIM(profile_postcodes.state)) = UPPER(TRIM(
                            COALESCE(
                                NULLIF(
                                    CASE
                                        WHEN provider_profiles.suburb LIKE '%,%' THEN SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(provider_profiles.suburb, ',', -1)), ' ', 1)
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
            ->where('provider_profiles.is_blocked', false)
            ->leftJoin('online_users', 'online_users.provider_profile_id', '=', 'provider_profiles.id')
            ->where(function ($q) {
                $q->whereNull('hide_show_profiles.id')
                    ->orWhere('hide_show_profiles.status', 'show');
            })
            ->where(function ($q) {
                $q->where(function ($onlineQ) {
                    $onlineQ->where('online_users.status', 'online');
                })->orWhere(function ($legacyQ) {
                    $legacyQ->whereExists(function ($exists): void {
                        $exists->selectRaw('1')
                            ->from('online_users as legacy_online_users')
                            ->whereColumn('legacy_online_users.user_id', 'provider_profiles.user_id')
                            ->whereNull('legacy_online_users.provider_profile_id')
                            ->where('legacy_online_users.status', 'online');
                    });
                });
            })
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
                ->where('profile_status', 'approved')
                ->where('is_blocked', false);

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

    private function resolveLocalFeaturedStateName(string $locationQuery, string $locationStateQuery): ?string
    {
        $state = trim($locationStateQuery);

        if ($state === '' && $locationQuery !== '' && str_contains($locationQuery, ',')) {
            $parts = explode(',', $locationQuery, 2);
            $state = trim($parts[1] ?? '');
        }

        if ($state === '') {
            return null;
        }

        $normalizedState = $this->normalizeStateAbbreviation($state) ?? strtoupper($state);

        return $this->resolveStateName($normalizedState);
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
            })->orWhere(function (Builder $profileQuery) use ($suburb, $state): void {
                $profileQuery->whereRaw(
                    'LOWER(TRIM(provider_profiles.suburb)) LIKE ?',
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

    public function getProfilesBySlugs(array $profileIds): array
    {
        if (empty($profileIds)) {
            return [];
        }

        $profiles = ProviderProfile::query()
            ->whereNull('provider_profiles.deleted_at')
            ->where('provider_profiles.profile_status', 'approved')
            ->where('provider_profiles.is_blocked', false)
            ->whereIn('provider_profiles.id', $profileIds)
            ->with([
                'profileImages' => fn ($q) => $q->orderByDesc('is_primary'),
                'photoVerification' => fn ($q) => $q->where('status', 'approved')->orderByDesc('submitted_at'),
                'rates',
                'onlineUser',
                'availableNow',
                'user',
                'user.onlineUser',
                'city',
                'state',
            ])
            ->get();

        $serviceIds = $profiles
            ->flatMap(fn (ProviderProfile $p) => array_filter((array) ($p->services_provided ?? []), 'is_numeric'))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();

        $categoryNames = $serviceIds
            ? Category::query()->whereIn('id', $serviceIds)->pluck('name', 'id')
            : collect();

        // Preserve the user's saved order
        $profileIdOrder = array_flip(array_map('strval', $profileIds));

        return $profiles
            ->map(fn (ProviderProfile $profile) => $this->transformProfile($profile, $categoryNames))
            ->sortBy(fn ($profile) => $profileIdOrder[(string) $profile['id']] ?? PHP_INT_MAX)
            ->values()
            ->all();
    }

    private function transformProfile(ProviderProfile $profile, Collection $categoryNames): array
    {
        $primaryImage = $profile->profileImages?->first();
        $imageUrl = $primaryImage?->thumbnail_url ?? $primaryImage?->image_url ?? null;

        $firstRate = $profile->rates?->first();
        $rateDisplay = $this->formatRate($firstRate);

        $services = $this->resolveIds(
            array_values(array_filter((array) ($profile->services_provided ?? []))),
            $categoryNames
        );

        $isOnline = $profile->isCurrentlyOnline();
        $isAvailableNow = $profile->availableNow?->isCurrentlyAvailable() ?? false;
        $isPhotoVerified = $profile->relationLoaded('photoVerification')
            ? $profile->photoVerification->isNotEmpty()
            : $profile->photoVerification()->where('status', 'approved')->exists();

        return [
            'id' => $profile->id,
            'name' => $profile->name,
            'age' => $profile->age,
            'rate' => $rateDisplay,
            'rate_numeric' => $this->extractNumericRate($firstRate),
            'in_call' => trim((string) ($firstRate?->incall ?? '')),
            'out_call' => trim((string) ($firstRate?->outcall ?? '')),
            'city' => $profile->city?->name ?? '',
            'suburb' => $this->extractSuburbName((string) ($profile->suburb ?? '')),
            'distance_km' => isset($profile->distance_km) ? round((float) $profile->distance_km, 1) : null,
            'height' => '',
            'service_1' => $services[0] ?? '',
            'service_2' => $services[1] ?? '',
            'date' => $profile->created_at->format('d/m/Y'),
            'description' => $profile->description ?? '',
            'active' => $isOnline,
            'available_now' => $isOnline || $isAvailableNow,
            'verified' => $isPhotoVerified,
            'featured' => (bool) $profile->is_featured,
            'home_featured' => $profile->home_featured_expires_at && $profile->home_featured_expires_at->isFuture(),
            'local_banner' => $profile->local_banner_expires_at && $profile->local_banner_expires_at->isFuture(),
            'home_banner' => $profile->home_banner_expires_at && $profile->home_banner_expires_at->isFuture(),
            'image' => $imageUrl ?? '',
            'slug' => $profile->slug,
            'profile_url' => $profile->getEscortUrl(),
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
