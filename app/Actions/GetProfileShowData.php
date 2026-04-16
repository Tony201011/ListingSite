<?php

namespace App\Actions;

use App\Concerns\ResolvesProfileCategoryIds;
use App\Models\Category;
use App\Models\ProviderProfile;
use Illuminate\Support\Collection;

class GetProfileShowData
{
    use ResolvesProfileCategoryIds;

    public function execute(string $slug, array $validated): array
    {
        $providerProfile = ProviderProfile::query()
            ->where('slug', $slug)
            ->with([
                'user.profileImages',
                'user.primaryProfileImage',
                'user.rates.group',
                'user.availabilities',
                'user.tours',
                'user.userVideos',
                'user.onlineUser',
                'user.availableNow',
                'user.profileMessage',

                'city',
                'state',
                'country',
            ])
            ->first();

        abort_if($providerProfile === null, 404);

        $categoryIds = array_filter([
            $providerProfile->age_group_id,
            $providerProfile->hair_color_id,
            $providerProfile->hair_length_id,
            $providerProfile->ethnicity_id,
            $providerProfile->body_type_id,
            $providerProfile->bust_size_id,
            $providerProfile->your_length_id,
        ]);

        $arrayFieldIds = collect([
            ...(array) ($providerProfile->primary_identity ?? []),
            ...(array) ($providerProfile->attributes ?? []),
            ...(array) ($providerProfile->services_style ?? []),
            ...(array) ($providerProfile->services_provided ?? []),
        ])->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();

        $allCategoryIds = array_unique(array_merge($categoryIds, $arrayFieldIds));

        $categoryNames = Category::query()
            ->whereIn('id', array_filter($allCategoryIds))
            ->pluck('name', 'id');

        $profile = $this->buildProfileArray($providerProfile, $categoryNames);

        $prevProfile = $this->getAdjacentProfile($providerProfile->id, 'prev');
        $nextProfile = $this->getAdjacentProfile($providerProfile->id, 'next');

        $nearbyProfiles = $this->getNearbyProfiles($providerProfile->id, $providerProfile->city_id);

        $selectedCategoryIds = collect($validated['categories'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        $selectedCategoryNames = $selectedCategoryIds->isNotEmpty()
            ? Category::query()
                ->whereIn('id', $selectedCategoryIds)
                ->orderBy('name')
                ->pluck('name')
                ->values()
                ->all()
            : [];

        $selectedCategoriesByGroup = [];

        if ($selectedCategoryIds->isNotEmpty()) {
            $selectedCategories = Category::query()
                ->whereIn('id', $selectedCategoryIds)
                ->get(['id', 'name', 'parent_id']);

            $parentNames = Category::query()
                ->whereIn('id', $selectedCategories->pluck('parent_id')->filter()->unique())
                ->pluck('name', 'id');

            $selectedCategoriesByGroup = $selectedCategories
                ->groupBy(fn ($category) => (int) ($category->parent_id ?? 0))
                ->map(function ($items, $parentId) use ($parentNames) {
                    $heading = (int) $parentId > 0
                        ? (string) ($parentNames->get((int) $parentId) ?? 'Other')
                        : 'Other';

                    return [
                        'heading' => $heading,
                        'items' => $items
                            ->pluck('name')
                            ->filter()
                            ->map(fn ($name) => trim((string) $name))
                            ->unique()
                            ->take(2)
                            ->values()
                            ->all(),
                    ];
                })
                ->filter(fn ($group) => ! empty($group['items']))
                ->sortBy('heading')
                ->values()
                ->all();
        }

        $profileStats = [
            ['label' => 'Age group', 'value' => $categoryNames->get($providerProfile->age_group_id) ?? '—'],
            ['label' => 'Ethnicity', 'value' => $categoryNames->get($providerProfile->ethnicity_id) ?? '—'],
            ['label' => 'Hair color', 'value' => $categoryNames->get($providerProfile->hair_color_id) ?? '—'],
            ['label' => 'Hair length', 'value' => $categoryNames->get($providerProfile->hair_length_id) ?? '—'],
            ['label' => 'Body type', 'value' => $categoryNames->get($providerProfile->body_type_id) ?? '—'],
            ['label' => 'Bust size', 'value' => $categoryNames->get($providerProfile->bust_size_id) ?? '—'],
            ['label' => 'Length', 'value' => $categoryNames->get($providerProfile->your_length_id) ?? '—'],
        ];

        //  dd($profile);

        return [
            'profile' => $profile,
            'nearbyProfiles' => $nearbyProfiles,
            'selectedCategoryNames' => $selectedCategoryNames,
            'selectedCategoriesByGroup' => $selectedCategoriesByGroup,
            'profileStats' => $profileStats,
            'prevProfile' => $prevProfile,
            'nextProfile' => $nextProfile,
        ];
    }

    private function buildProfileArray(ProviderProfile $providerProfile, Collection $categoryNames): array
    {
        $user = $providerProfile->user;

        $images = $user?->profileImages
            ?->sortByDesc('is_primary')
            ->map(fn ($img) => $img->image_url)
            ->filter()
            ->values()
            ->all() ?? [];

        $videos = $user?->userVideos
            ?->map(fn ($v) => $v->video_url)
            ->filter()
            ->values()
            ->all() ?? [];

        $rates = $user?->rates ?? collect();

        $priceList = $rates->map(fn ($rate) => [
            'description' => $rate->description ?? '',
            'incall' => $rate->incall ?? '',
            'outcall' => $rate->outcall ?? '',
            'extra' => $rate->extra ?? '',
            'group' => $rate->group?->name ?? '',
        ])->values()->all();

        $availabilities = $user?->availabilities ?? collect();
        $availabilityList = $availabilities->map(function ($avail) {
            if ($avail->by_appointment) {
                $time = 'By appointment';
            } elseif ($avail->all_day) {
                $time = 'All day';
            } elseif (! $avail->enabled) {
                $time = 'Unavailable';
            } else {
                $from = $avail->from_time ? \Carbon\Carbon::parse($avail->from_time)->format('H:i') : '';
                if ($avail->till_late) {
                    $to = 'Late';
                } elseif ($avail->to_time) {
                    $to = \Carbon\Carbon::parse($avail->to_time)->format('H:i');
                } else {
                    $to = '';
                }
                $time = $from && $to ? "{$from} - {$to}" : ($from ?: 'Unavailable');
            }

            return ['day' => $avail->day, 'time' => $time];
        })->values()->all();

        $tours = $user?->tours
            ?->where('enabled', true)
            ->sortBy('from')
            ->map(fn ($tour) => [
                'city' => $tour->city,
                'from' => $tour->from?->format('D d M'),
                'to' => $tour->to?->format('D d M'),
                'description' => $tour->description ?? '',
            ])
            ->values()
            ->all() ?? [];

        $services = $this->resolveIds((array) ($providerProfile->services_provided ?? []), $categoryNames);

        $firstRate = $rates->first();
        $rateDisplay = $this->formatRate($firstRate);

        $primaryImage = $user?->primaryProfileImage;
        $primaryImageUrl = $primaryImage?->image_url ?? ($images[0] ?? null);

        return [
            'id' => $providerProfile->id,
            'slug' => $providerProfile->slug,
            'name' => $providerProfile->name,
            'age' => $providerProfile->age,
            'description' => $providerProfile->description ?? '',
            'about' => $providerProfile->profile_text ?? $providerProfile->description ?? '',
            'introduction_line' => $providerProfile->introduction_line ?? '',
            'city' => $providerProfile->city?->name ?? '',
            'state' => $providerProfile->state?->name ?? '',
            'country' => $providerProfile->country?->name ?? '',
            'phone' => $providerProfile->phone ?? '',
            'whatsapp' => $providerProfile->whatsapp ?? '',
            'website' => $providerProfile->website ?? '',
            'twitter' => $providerProfile->twitter_handle ? "https://twitter.com/{$providerProfile->twitter_handle}" : '',
            'onlyfans' => $providerProfile->onlyfans_username ? "https://onlyfans.com/{$providerProfile->onlyfans_username}" : '',
            'is_verified' => $providerProfile->is_verified,
            'is_featured' => $providerProfile->is_featured,
            'image' => $primaryImageUrl ?? '',
            'images' => $images,
            'videos' => $videos,
            'rate' => $rateDisplay,
            'price_list' => $priceList,
            'availability_list' => $availabilityList,
            'tours' => $tours,
            'service_1' => $services[0] ?? '',
            'service_2' => $services[1] ?? '',
            'services_provided' => $services,
            'services_style' => $this->resolveIds((array) ($providerProfile->services_style ?? []), $categoryNames),
            'primary_identity' => $this->resolveIds((array) ($providerProfile->primary_identity ?? []), $categoryNames),
            'attributes' => $this->resolveIds((array) ($providerProfile->attributes ?? []), $categoryNames),
            'ethnicity' => $categoryNames->get($providerProfile->ethnicity_id) ?? '',
            'hair_color' => $categoryNames->get($providerProfile->hair_color_id) ?? '',
            'hair_length' => $categoryNames->get($providerProfile->hair_length_id) ?? '',
            'body_type' => $categoryNames->get($providerProfile->body_type_id) ?? '',
            'bust_size' => $categoryNames->get($providerProfile->bust_size_id) ?? '',
            'your_length' => $categoryNames->get($providerProfile->your_length_id) ?? '',
            'age_group' => $categoryNames->get($providerProfile->age_group_id) ?? '',
            'profile_message' => $user?->profileMessage?->message ?? '',
            'active' => $user?->onlineUser?->isCurrentlyOnline() ?? false,
            'available_now' => $user?->availableNow?->isCurrentlyAvailable() ?? false,
            'available_expires_at' => $user?->availableNow?->isCurrentlyAvailable()
                ? $user->availableNow->available_expires_at
                : null,
            'suburb' => $user?->suburb ?? '',
            'contact_method' => $providerProfile->contact_method ?? '',
            'phone_contact_preference' => $providerProfile->phone_contact_preference ?? '',
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

    private function getAdjacentProfile(int $currentId, string $direction): array
    {
        if ($direction === 'prev') {
            $adjacent = ProviderProfile::query()
                ->where('profile_status', 'approved')
                ->whereHas('user')
                ->where('id', '<', $currentId)
                ->orderByDesc('id')
                ->first(['id', 'name', 'slug']);

            if ($adjacent === null) {
                $adjacent = ProviderProfile::query()
                    ->where('profile_status', 'approved')
                    ->whereHas('user')
                    ->orderByDesc('id')
                    ->first(['id', 'name', 'slug']);
            }
        } else {
            $adjacent = ProviderProfile::query()
                ->where('profile_status', 'approved')
                ->whereHas('user')
                ->where('id', '>', $currentId)
                ->orderBy('id')
                ->first(['id', 'name', 'slug']);

            if ($adjacent === null) {
                $adjacent = ProviderProfile::query()
                    ->where('profile_status', 'approved')
                    ->whereHas('user')
                    ->orderBy('id')
                    ->first(['id', 'name', 'slug']);
            }
        }

        if ($adjacent === null) {
            return ['slug' => '', 'name' => ''];
        }

        return [
            'slug' => $adjacent->slug ?? '',
            'name' => $adjacent->name ?? '',
        ];
    }

    private function getNearbyProfiles(int $currentId, ?int $cityId): array
    {
        return ProviderProfile::query()
            ->where('id', '!=', $currentId)
            ->where('profile_status', 'approved')
            ->when($cityId, fn ($q) => $q->where('city_id', $cityId))
            ->whereHas('user')
            ->with([
                'user.primaryProfileImage',
                'city',
            ])
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->limit(4)
            ->get()
            ->map(function (ProviderProfile $profile) {
                $primaryImage = $profile->user?->primaryProfileImage;
                $imageUrl = $primaryImage?->image_url ?? null;

                $services = array_values(array_filter((array) ($profile->services_provided ?? [])));

                return [
                    'slug' => $profile->slug ?? '',
                    'name' => $profile->name ?? '',
                    'image' => $imageUrl ?? '',
                    'city' => $profile->city?->name ?? '',
                    'service_1' => $services[0] ?? '',
                    'rate' => 'Contact for rate',
                ];
            })
            ->values()
            ->all();
    }
}
