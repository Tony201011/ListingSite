<?php

namespace App\Actions;

use App\Models\Category;
use App\Models\ProviderProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BuildProfileFilterViewData
{
    private const PROFILES_PER_PAGE = 12;

    private const SLUG_TO_COLUMN = [
        'hair-color' => 'hair_color_id',
        'hair-length' => 'hair_length_id',
        'ethnicity' => 'ethnicity_id',
        'body-type' => 'body_type_id',
        'bust-size' => 'bust_size_id',
        'your-length' => 'your_length_id',
    ];

    private const SLUG_TO_JSON_COLUMN = [
        'primary-identity'     => 'primary_identity',
        'attributes'           => 'attributes',
        'services-style'       => 'services_style',
        'services-you-provide' => 'services_provided',
    ];

    private const SLUG_TO_STRING_COLUMN = [
        'availability'              => 'availability',
        'contact-method'            => 'contact_method',
        'phone-contact-preferences' => 'phone_contact_preference',
        'time-waster-shield'        => 'time_waster_shield',
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

        $minAge = (int) ($validated['min_age'] ?? 18);
        $maxAge = (int) ($validated['max_age'] ?? 40);
        $minPrice = (int) ($validated['min_price'] ?? 150);
        $maxPrice = (int) ($validated['max_price'] ?? 400);

        if ($minAge > $maxAge) {
            [$minAge, $maxAge] = [$maxAge, $minAge];
        }

        if ($minPrice > $maxPrice) {
            [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
        }

        $locationQuery = (string) ($validated['location'] ?? '');
        $escortNameQuery = (string) ($validated['escort_name'] ?? '');

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
        );

        $allFilterCategoriesCollection = collect($allFilterCategories);

        $selectedCategoryItems = $allFilterCategoriesCollection
            ->whereIn('id', $selectedCategoryIds)
            ->values();

        $hasAgeFilter = $minAge !== 18 || $maxAge !== 40;
        $hasPriceFilter = $minPrice !== 150 || $maxPrice !== 400;

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
        );
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
    ): LengthAwarePaginator {
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

        if ($locationQuery !== '') {
            $query->where(function ($q) use ($locationQuery) {
                $q->whereHas('city', fn ($q) => $q->where('name', 'like', '%' . $locationQuery . '%'))
                  ->orWhereHas('user', fn ($q) => $q->where('suburb', 'like', '%' . $locationQuery . '%'));
            });
        }

        if ($escortNameQuery !== '') {
            $query->where('name', 'like', '%' . $escortNameQuery . '%');
        }

        if ($minAge > 18 || $maxAge < 40) {
            $query->whereBetween('age', [$minAge, $maxAge]);
        }

        if ($minPrice !== 150 || $maxPrice !== 400) {
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

        $appendParams = array_filter([
            'location' => $locationQuery ?: null,
            'escort_name' => $escortNameQuery ?: null,
            'min_age' => $minAge !== 18 ? $minAge : null,
            'max_age' => $maxAge !== 40 ? $maxAge : null,
            'min_price' => $minPrice !== 150 ? $minPrice : null,
            'max_price' => $maxPrice !== 400 ? $maxPrice : null,
        ]);

        foreach ($selectedCategoryIds as $categoryId) {
            $appendParams['categories'][] = $categoryId;
        }

        $paginator = $query
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->paginate(self::PROFILES_PER_PAGE)
            ->appends($appendParams);

        $paginator->getCollection()->transform(fn (ProviderProfile $profile) => $this->transformProfile($profile));

        return $paginator;
    }

    private function transformProfile(ProviderProfile $profile): array
    {
        $primaryImage = $profile->user?->profileImages?->first();
        $imageUrl = $primaryImage?->thumbnail_url ?? $primaryImage?->image_url ?? null;

        $firstRate = $profile->user?->rates?->first();
        $rateDisplay = $this->formatRate($firstRate);

        $services = array_values(array_filter((array) ($profile->services_provided ?? [])));

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
