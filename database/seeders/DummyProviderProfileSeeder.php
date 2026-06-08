<?php

namespace Database\Seeders;

use App\Models\Availability;
use App\Models\AvailableNow;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\OnlineUser;
use App\Models\ProfileImage;
use App\Models\ProfileMessage;
use App\Models\ProviderListing;
use App\Models\ProviderProfile;
use App\Models\Rate;
use App\Models\RateGroup;
use App\Models\SiteSetting;
use App\Models\State;
use App\Models\Tour;
use App\Models\TourCity;
use App\Models\User;
use App\Models\UserVideo;
use App\Support\EscortLocationData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DummyProviderProfileSeeder extends Seeder
{
    private const TOTAL = 1000;
    private const PROFILES_PER_ACCOUNT = 3;

    private const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    private const RATE_DESCRIPTIONS = ['30 min', '1 hour', '2 hours', 'Overnight'];

    private const INCALL_PRICES = ['Demo', 'Demo', 'Demo', 'Demo'];

    private const OUTCALL_PRICES = ['Demo', 'Demo', 'Demo', 'Demo'];

    private const AVAILABILITY_OPTIONS = ['incalls-only', 'outcalls-only', 'incalls-and-outcalls'];

    private const CONTACT_METHOD_OPTIONS = ['phone-only', 'email-contact-form-only-phone-hidden'];

    private const PHONE_PREF_OPTIONS = ['accept-calls-sms', 'accept-calls-only', 'accept-sms-only'];

    private const TIME_WASTER_OPTIONS = ['no', 'yes'];

    private const WEBSITE_TYPES = ['adult', 'porn'];

    private const ONLINE_SESSION_DURATION_MINUTES = 60;

    private const SAMPLE_VIDEOS = [
        'https://www.w3schools.com/html/mov_bbb.mp4',
        'https://www.w3schools.com/html/movie.mp4',
        'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_1mb.mp4',
        'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_2mb.mp4',
        'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_5mb.mp4',
        'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_10mb.mp4',
        'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_20mb.mp4',
        'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_30mb.mp4',
        'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_50mb.mp4',
        'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_1mb.mp4',
    ];

    private const FEMALE_NAMES = [
        'Amber', 'Bella', 'Chloe', 'Diana', 'Elena', 'Fiona', 'Grace', 'Hannah', 'Iris', 'Jade',
        'Kelly', 'Luna', 'Mia', 'Nina', 'Olivia', 'Petra', 'Quinn', 'Rosa', 'Sophie', 'Tara',
        'Uma', 'Vera', 'Willow', 'Xena', 'Yasmin', 'Zara', 'Alexis', 'Brooke', 'Carmen', 'Daisy',
        'Eva', 'Faith', 'Gina', 'Holly', 'Isla', 'Jasmine', 'Kate', 'Leila', 'Monica', 'Natasha',
        'Opal', 'Paris', 'Ruby', 'Stella', 'Tina', 'Ursula', 'Victoria', 'Wendy', 'Xia', 'Yvette',
    ];

    public function run(): void
    {
        $freeListingDays = (int) (SiteSetting::getAdTierSettings()['free_listing_days'] ?? 21);

        $stateNameMap = [
            'ACT' => 'Australian Capital Territory',
            'NSW' => 'New South Wales',
            'VIC' => 'Victoria',
            'QLD' => 'Queensland',
            'WA' => 'Western Australia',
            'SA' => 'South Australia',
            'TAS' => 'Tasmania',
            'NT' => 'Northern Territory',
        ];

        $categoryIds = Category::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->pluck('id')
            ->values()
            ->all();

        $australia = Country::query()
            ->where(function ($query): void {
                $query->where('code', 'AU')
                    ->orWhere('name', 'Australia');
            })
            ->first();
        $cityRows = City::query()->with('state.country')->get();
        $stateRows = State::query()->with('country')->get();
        $seedLocations = collect(EscortLocationData::profileLocations())
            ->map(function (array $location) use ($australia, $cityRows, $stateNameMap, $stateRows): array {
                $stateCode = $location['state'];
                $stateName = $stateNameMap[$stateCode] ?? $stateCode;
                $stateModel = $stateRows->first(
                    fn (State $state): bool => strcasecmp($state->name, $stateName) === 0
                        && strcasecmp((string) ($state->country?->code ?? ''), 'AU') === 0
                );
                $cityModel = $cityRows->first(
                    fn (City $city): bool => strcasecmp($city->name, $location['suburb']) === 0
                        && strcasecmp((string) $city->state?->name, $stateName) === 0
                        && strcasecmp((string) ($city->state?->country?->code ?? ''), 'AU') === 0
                );

                return [
                    ...$location,
                    'city_id' => $cityModel?->id,
                    'state_id' => $stateModel?->id,
                    'country_id' => $stateModel?->country_id ?? $australia?->id,
                    'suburb_value' => EscortLocationData::formatProfileSuburb($location),
                ];
            })
            ->values();
        $melbourneLocation = $seedLocations->first(
            fn (array $location): bool => $location['suburb'] === 'Melbourne' && $location['state'] === 'VIC'
        );

        $ageGroupIds = Category::query()->where('slug', 'age-group')
            ->first()?->children()->pluck('id')->values()->all() ?? [];
        $hairColorIds = Category::query()->where('slug', 'hair-color')
            ->first()?->children()->pluck('id')->values()->all() ?? [];
        $hairLengthIds = Category::query()->where('slug', 'hair-length')
            ->first()?->children()->pluck('id')->values()->all() ?? [];
        $ethnicityIds = Category::query()->where('slug', 'ethnicity')
            ->first()?->children()->pluck('id')->values()->all() ?? [];
        $bodyTypeIds = Category::query()->where('slug', 'body-type')
            ->first()?->children()->pluck('id')->values()->all() ?? [];
        $bustSizeIds = Category::query()->where('slug', 'bust-size')
            ->first()?->children()->pluck('id')->values()->all() ?? [];
        $yourLengthIds = Category::query()->where('slug', 'your-length')
            ->first()?->children()->pluck('id')->values()->all() ?? [];

        $tourCityNames = TourCity::query()->pluck('name')->values()->all();
        if (empty($tourCityNames)) {
            $tourCityNames = $seedLocations->pluck('suburb')->all();
        }

        $primaryIdentityIds = Category::query()->where('slug', 'primary-identity')
            ->first()?->children()->pluck('id')->values()->all() ?? [];
        $attributeIds = Category::query()->where('slug', 'attributes')
            ->first()?->children()->pluck('id')->values()->all() ?? [];
        for ($i = 1; $i <= self::TOTAL; $i++) {
            $accountIndex = intdiv($i - 1, self::PROFILES_PER_ACCOUNT) + 1;
            $profileNumberWithinAccount = (($i - 1) % self::PROFILES_PER_ACCOUNT) + 1;
            $email = "provider{$accountIndex}@yopmail.com";
            $name = $this->pickName($i);
            $seedLocation = $seedLocations[($i - 1) % max($seedLocations->count(), 1)];
            $isJerryProfile = $i === 90 && $melbourneLocation !== null;

            if ($isJerryProfile) {
                $name = 'Jerry09090';
                $seedLocation = $melbourneLocation;
            }

            // 1. User
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $this->pickName($accountIndex),
                    'password' => bcrypt('Provider@12345'),
                    'role' => User::ROLE_PROVIDER,
                    'is_blocked' => false,
                    'mobile' => sprintf('+61 4%02d %03d %03d', $accountIndex % 100, ($accountIndex * 7) % 1000, ($accountIndex * 13) % 1000),
                    'mobile_verified' => true,
                    'email_verified_at' => now(),
                    'account_status' => 'active',
                ],
            );

            // 2. ProviderProfile
            $cityId = $seedLocation['city_id'] ?? null;
            $stateId = $seedLocation['state_id'] ?? null;
            $countryId = $seedLocation['country_id'] ?? $australia?->id;

            $slug = Str::slug($name) ?: 'profile';
            $existingProfile = ProviderProfile::withTrashed()
                ->where('user_id', $user->id)
                ->where('name', $name)
                ->first();
            $profileSequence = $existingProfile?->profile_sequence
                ?? ((ProviderProfile::withTrashed()->where('slug', $slug)->max('profile_sequence') ?? 0) + 1);

            $providerProfile = ProviderProfile::updateOrCreate(
                ['user_id' => $user->id, 'name' => $name],
                [
                    'name' => $name,
                    'slug' => $slug,
                    'profile_sequence' => $profileSequence,
                    'suburb' => $seedLocation['suburb_value'],
                    'age' => rand(21, 45),
                    'description' => "Demo profile for {$name} used to showcase listing layout, filters, and advertising placements only.",
                    'introduction_line' => "Demo listing for {$name} (sample data only).",
                    'profile_text' => 'Sample content only. This profile demonstrates page structure and ad placement behaviour in a test environment.',
                    'primary_identity' => count($primaryIdentityIds) > 0
                        ? [$this->pickFrom($primaryIdentityIds, $i)]
                        : [],
                    'attributes' => $this->pickMultiple($attributeIds, $i, 3),
                    'services_style' => [],
                    'services_provided' => [],
                    'age_group_id' => count($ageGroupIds) > 0 ? $this->pickFrom($ageGroupIds, $i) : null,
                    'hair_color_id' => count($hairColorIds) > 0 ? $this->pickFrom($hairColorIds, $i) : null,
                    'hair_length_id' => count($hairLengthIds) > 0 ? $this->pickFrom($hairLengthIds, $i) : null,
                    'ethnicity_id' => count($ethnicityIds) > 0 ? $this->pickFrom($ethnicityIds, $i) : null,
                    'body_type_id' => count($bodyTypeIds) > 0 ? $this->pickFrom($bodyTypeIds, $i) : null,
                    'bust_size_id' => count($bustSizeIds) > 0 ? $this->pickFrom($bustSizeIds, $i) : null,
                    'your_length_id' => count($yourLengthIds) > 0 ? $this->pickFrom($yourLengthIds, $i) : null,
                    'availability' => $this->pickFrom(self::AVAILABILITY_OPTIONS, $i),
                    'contact_method' => $this->pickFrom(self::CONTACT_METHOD_OPTIONS, $i),
                    'phone_contact_preference' => $this->pickFrom(self::PHONE_PREF_OPTIONS, $i),
                    'time_waster_shield' => $this->pickFrom(self::TIME_WASTER_OPTIONS, $i),
                    'twitter_handle' => '@'.Str::lower(str_replace(' ', '', $name)).$i,
                    'website' => null,
                    'onlyfans_username' => Str::lower(str_replace(' ', '', $name)).$i,
                    'country_id' => $countryId,
                    'state_id' => $stateId,
                    'city_id' => $cityId,
                    'latitude' => round(-33.8688 + ($i * 0.01), 7),
                    'longitude' => round(151.2093 + ($i * 0.01), 7),
                    'phone' => null,
                    'whatsapp' => null,
                    'is_verified' => $i % 3 === 0,
                    'is_featured' => false,
                    'featured_expires_at' => null,
                    'home_featured_expires_at' => null,
                    'local_banner_expires_at' => null,
                    'home_banner_expires_at' => null,
                    'free_listing_expires_at' => now()->addDays($freeListingDays),
                    'profile_status' => 'approved',
                    'expires_at' => now()->addMonths(rand(1, 12)),
                ],
            );

            // 3. ProviderListing
            $categoryId = count($categoryIds) > 0 ? $categoryIds[$i % count($categoryIds)] : null;
            $thumbnailUrl = "https://picsum.photos/seed/listing-{$i}/512/512";

            if ($profileNumberWithinAccount === 1) {
                ProviderListing::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'title' => "{$name} Demo Listing",
                        'age' => rand(21, 45),
                        'category_id' => $categoryId,
                        'website_type' => $this->pickFrom(self::WEBSITE_TYPES, $i),
                        'audience_score' => rand(60, 98),
                        'thumbnail' => $thumbnailUrl,
                        'is_live' => $i % 3 === 0,
                        'is_vip' => $i <= 10,
                        'is_active' => true,
                    ],
                );
            }

            // 4. RateGroup + Rates
            $rateGroup = RateGroup::updateOrCreate(
                ['provider_profile_id' => $providerProfile->id, 'name' => 'Standard Rates'],
                ['user_id' => $user->id],
            );

            foreach (self::RATE_DESCRIPTIONS as $rateIndex => $description) {
                Rate::updateOrCreate(
                    ['provider_profile_id' => $providerProfile->id, 'description' => $description],
                    [
                        'user_id' => $user->id,
                        'incall' => self::INCALL_PRICES[$rateIndex],
                        'outcall' => self::OUTCALL_PRICES[$rateIndex],
                        'extra' => $rateIndex === count(self::RATE_DESCRIPTIONS) - 1
                            ? 'Demo tier information for layout preview only'
                            : null,
                        'group_id' => $rateGroup->id,
                    ],
                );
            }

            // 5. Availability (all 7 days)
            foreach (self::DAYS as $dayIndex => $day) {
                $enabled = $dayIndex < 5 || $i % 2 === 0; // weekdays always on; weekends alternate
                $allDay = $enabled && $i % 7 === 0;
                $tillLate = $enabled && $i % 5 === 0;

                Availability::updateOrCreate(
                    ['provider_profile_id' => $providerProfile->id, 'day' => $day],
                    [
                        'user_id' => $user->id,
                        'enabled' => $enabled,
                        'from_time' => ($enabled && ! $allDay) ? '09:00' : null,
                        'to_time' => ($enabled && ! $allDay && ! $tillLate) ? '22:00' : null,
                        'till_late' => $tillLate,
                        'all_day' => $allDay,
                        'by_appointment' => $dayIndex >= 5 && ! $enabled,
                    ],
                );
            }

            // 6. Profile images (3 per provider; first is primary and also stored as user avatar)
            // Force-delete existing images to avoid the uq_one_primary_per_user constraint on re-runs.
            ProfileImage::where('provider_profile_id', $providerProfile->id)->forceDelete();

            for ($imgIndex = 1; $imgIndex <= 3; $imgIndex++) {
                $isPrimary = $imgIndex === 1;
                $imageUrl = "https://picsum.photos/seed/profile-{$i}-{$imgIndex}/400/400";
                $thumbUrl = "https://picsum.photos/seed/thumb-{$i}-{$imgIndex}/200/200";

                ProfileImage::create([
                    'user_id' => $user->id,
                    'provider_profile_id' => $providerProfile->id,
                    'image_path' => $imageUrl,
                    'thumbnail_path' => $thumbUrl,
                    'is_primary' => $isPrimary,
                ]);
            }

            // Set user profile_image to the primary image URL
            $user->update(['profile_image' => "https://picsum.photos/seed/profile-{$i}-1/400/400"]);

            // 6b. Profile message
            ProfileMessage::updateOrCreate(
                ['provider_profile_id' => $providerProfile->id],
                [
                    'user_id' => $user->id,
                    'message' => "Hi there! I'm {$name}. This inbox copy is demo-only and is shown to preview message card layout.",
                ],
            );

            // 7. User videos (2 per provider)
            for ($vidIndex = 1; $vidIndex <= 2; $vidIndex++) {
                $videoUrl = $this->pickFrom(self::SAMPLE_VIDEOS, $i + $vidIndex);

                UserVideo::updateOrCreate(
                    ['provider_profile_id' => $providerProfile->id, 'video_path' => $videoUrl],
                    [
                        'user_id' => $user->id,
                        'original_name' => "video-{$i}-{$vidIndex}.mp4",
                    ],
                );
            }

            // 8. Tours (2 per provider)
            for ($tourIndex = 1; $tourIndex <= 2; $tourIndex++) {
                $city = $this->pickFrom($tourCityNames, $i + $tourIndex);
                $from = now()->addDays(($i % 30) + $tourIndex)->setTime(10, 0);
                $to = (clone $from)->addDays(rand(2, 7))->setTime(18, 0);

                Tour::updateOrCreate(
                    ['provider_profile_id' => $providerProfile->id, 'city' => $city, 'from' => $from],
                    [
                        'user_id' => $user->id,
                        'to' => $to,
                        'description' => "Demo travel note for {$city}. This sample text is for UI preview only.",
                        'enabled' => true,
                    ],
                );
            }

            // 9. AvailableNow (one per profile, set to offline by default)
            AvailableNow::updateOrCreate(
                ['provider_profile_id' => $providerProfile->id],
                [
                    'user_id' => $user->id,
                    'status' => 'offline',
                ],
            );

            // 10. OnlineUser (keep all seeded profiles online so seeded profile URLs are publicly accessible)
            $isOnline = true;

            OnlineUser::updateOrCreate(
                ['provider_profile_id' => $providerProfile->id],
                [
                    'user_id' => $user->id,
                    'status' => $isOnline ? 'online' : 'offline',
                    'usage_date' => today(),
                    'usage_count' => $isOnline ? 1 : 0,
                    'online_started_at' => $isOnline ? now() : null,
                    'online_expires_at' => $isOnline ? now()->addMinutes(self::ONLINE_SESSION_DURATION_MINUTES) : null,
                ],
            );
        }
    }

    private function pickName(int $index): string
    {
        $names = self::FEMALE_NAMES;

        return $names[($index - 1) % count($names)].sprintf('%03d', $index);
    }

    private function pickFrom(array $items, int $index): mixed
    {
        if (empty($items)) {
            return null;
        }

        return $items[$index % count($items)];
    }

    /**
     * Pick $count items from $items starting at $index, wrapping around the array.
     */
    private function pickMultiple(array $items, int $index, int $count): array
    {
        if (empty($items)) {
            return [];
        }

        $result = [];
        $total = count($items);
        for ($j = 0; $j < min($count, $total); $j++) {
            $result[] = $items[($index + $j) % $total];
        }

        return array_values(array_unique($result));
    }
}
