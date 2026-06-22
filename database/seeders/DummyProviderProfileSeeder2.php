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
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds provider profiles from the scraped spreadsheet exported to
 * database/seeders/data/profiles_data.json.
 *
 * Unlike DummyProviderProfileSeeder (which generates synthetic data), this
 * seeder uses the real rows from the spreadsheet: name, location, phone,
 * about text, stats, tags, rates, images, socials, touring and availability.
 */
class DummyProviderProfileSeeder2 extends Seeder
{
    private const DATA_FILE = 'data/profiles_data.json';

    private const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    private const DAY_ABBR = [
        'MON' => 'Monday',
        'TUE' => 'Tuesday',
        'WED' => 'Wednesday',
        'THU' => 'Thursday',
        'FRI' => 'Friday',
        'SAT' => 'Saturday',
        'SUN' => 'Sunday',
    ];

    private const STATE_NAME_MAP = [
        'ACT' => 'Australian Capital Territory',
        'NSW' => 'New South Wales',
        'VIC' => 'Victoria',
        'QLD' => 'Queensland',
        'WA' => 'Western Australia',
        'SA' => 'South Australia',
        'TAS' => 'Tasmania',
        'NT' => 'Northern Territory',
    ];

    public function run(): void
    {
        $path = database_path('seeders/'.self::DATA_FILE);

        if (! is_file($path)) {
            $this->command?->error("Data file not found: {$path}");

            return;
        }

        /** @var array<int, array<string, string|null>> $records */
        $records = json_decode((string) file_get_contents($path), true) ?: [];

        $freeListingDays = (int) (SiteSetting::getAdTierSettings()['free_listing_days'] ?? 21);

        $australia = Country::query()
            ->where(function ($query): void {
                $query->where('code', 'AU')->orWhere('name', 'Australia');
            })
            ->first();

        $states = State::query()->with('country')->get();
        $cities = City::query()->with('state')->get();

        $ageGroupMap = $this->slugMap('age-group');
        $hairColorMap = $this->slugMap('hair-color');
        $hairLengthMap = $this->slugMap('hair-length');
        $ethnicityMap = $this->slugMap('ethnicity');
        $bodyTypeMap = $this->slugMap('body-type');
        $bustSizeMap = $this->slugMap('bust-size');
        $yourLengthMap = $this->slugMap('your-length');
        $attributeMap = $this->slugMap('attributes');

        $categoryIds = Category::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->pluck('id')
            ->values()
            ->all();

        foreach (array_values($records) as $index => $record) {
            $i = $index + 1;
            $name = trim((string) ($record['Name'] ?? '')) ?: "Profile {$i}";
            $stateCode = strtoupper(trim((string) ($record['State'] ?? '')));
            $stateName = self::STATE_NAME_MAP[$stateCode] ?? $stateCode;
            $suburb = trim((string) ($record['Location'] ?? ''));

            $stateModel = $states->first(
                fn (State $state): bool => strcasecmp($state->name, $stateName) === 0
                    && strcasecmp((string) ($state->country?->code ?? ''), 'AU') === 0
            );
            $cityModel = $cities->first(
                fn (City $city): bool => strcasecmp($city->name, $suburb) === 0
                    && (int) $city->state_id === (int) ($stateModel?->id ?? 0)
            );

            $stats = $this->parseStats((string) ($record['Stats'] ?? ''));
            $social = $this->parseSocial((string) ($record['Social'] ?? ''));
            $about = (string) ($record['About Me'] ?? '');
            $personalMessage = (string) ($record['Personal Message'] ?? '');

            // 1. User (one account per spreadsheet row)
            $email = "profile2_{$i}@yopmail.com";
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name.' '.$i,
                    'password' => bcrypt('Provider@12345'),
                    'role' => User::ROLE_PROVIDER,
                    'is_blocked' => false,
                    'mobile' => $record['Phone'] ?? null,
                    'mobile_verified' => false,
                    'email_verified_at' => now(),
                    'account_status' => 'active',
                ],
            );

            // 2. ProviderProfile
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
                    'suburb' => $suburb !== '' ? $suburb : null,
                    'age' => $stats['age'],
                    'description' => $about !== '' ? $about : "Profile for {$name}.",
                    'introduction_line' => $personalMessage !== '' ? Str::limit($personalMessage, 250) : null,
                    'profile_text' => $about !== '' ? $about : null,
                    'primary_identity' => [],
                    'attributes' => $this->mapSlugs((string) ($record['Tags'] ?? ''), $attributeMap),
                    'services_style' => [],
                    'services_provided' => [],
                    'age_group_id' => $ageGroupMap[Str::slug($stats['age_group'] ?? '')] ?? null,
                    'hair_color_id' => $hairColorMap[Str::slug($stats['hair_color'] ?? '')] ?? null,
                    'hair_length_id' => $hairLengthMap[Str::slug($stats['hair_length'] ?? '')] ?? null,
                    'ethnicity_id' => $ethnicityMap[Str::slug($stats['ethnicity'] ?? '')] ?? null,
                    'body_type_id' => $bodyTypeMap[Str::slug($stats['body_type'] ?? '')] ?? null,
                    'bust_size_id' => $bustSizeMap[Str::slug($stats['bust_size'] ?? '')] ?? null,
                    'your_length_id' => $yourLengthMap[Str::slug($stats['length'] ?? '')] ?? null,
                    'availability' => $this->parseContactAvailability((string) ($record['Contact For'] ?? '')),
                    'contact_method' => 'phone-only',
                    'phone_contact_preference' => 'accept-calls-sms',
                    'time_waster_shield' => 'no',
                    'twitter_handle' => $social['twitter_handle'],
                    'website' => $social['website'],
                    'onlyfans_username' => $social['onlyfans_username'],
                    'country_id' => $stateModel?->country_id ?? $australia?->id,
                    'state_id' => $stateModel?->id,
                    'city_id' => $cityModel?->id,
                    'latitude' => null,
                    'longitude' => null,
                    'phone' => $record['Phone'] ?? null,
                    'whatsapp' => null,
                    'is_verified' => true,
                    'is_featured' => false,
                    'featured_expires_at' => null,
                    'home_featured_expires_at' => null,
                    'local_banner_expires_at' => null,
                    'home_banner_expires_at' => null,
                    'free_listing_expires_at' => now()->addDays($freeListingDays),
                    'profile_status' => 'approved',
                    'expires_at' => now()->addMonths(6),
                ],
            );

            // 3. ProviderListing
            $categoryId = count($categoryIds) > 0 ? $categoryIds[$index % count($categoryIds)] : null;
            $primaryImage = $this->parseImages((string) ($record['Image Sources'] ?? ''))[0] ?? null;
            ProviderListing::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'title' => "{$name} Listing",
                    'age' => $stats['age'],
                    'category_id' => $categoryId,
                    'website_type' => 'adult',
                    'audience_score' => rand(60, 98),
                    'thumbnail' => $primaryImage ?? "https://picsum.photos/seed/listing2-{$i}/512/512",
                    'is_live' => true,
                    'is_vip' => false,
                    'is_active' => true,
                ],
            );

            // 4. RateGroup + Rates (from spreadsheet, fallback to a placeholder)
            $rateGroup = RateGroup::updateOrCreate(
                ['provider_profile_id' => $providerProfile->id, 'name' => 'Standard Rates'],
                ['user_id' => $user->id],
            );

            Rate::where('provider_profile_id', $providerProfile->id)->delete();
            $rates = $this->parseRates((string) ($record['Rates'] ?? ''));
            if (empty($rates)) {
                $rates = [['description' => 'Enquire for rates', 'incall' => null, 'outcall' => null]];
            }
            foreach ($rates as $rate) {
                Rate::create([
                    'provider_profile_id' => $providerProfile->id,
                    'user_id' => $user->id,
                    'description' => $rate['description'],
                    'incall' => $rate['incall'],
                    'outcall' => $rate['outcall'],
                    'extra' => null,
                    'group_id' => $rateGroup->id,
                ]);
            }

            // 5. Availability (from spreadsheet weekday entries; default disabled)
            $availabilityByDay = $this->parseAvailability((string) ($record['Availability'] ?? ''));
            foreach (self::DAYS as $day) {
                $config = $availabilityByDay[$day] ?? null;
                Availability::updateOrCreate(
                    ['provider_profile_id' => $providerProfile->id, 'day' => $day],
                    [
                        'user_id' => $user->id,
                        'enabled' => $config['enabled'] ?? false,
                        'from_time' => $config['from_time'] ?? null,
                        'to_time' => $config['to_time'] ?? null,
                        'till_late' => $config['till_late'] ?? false,
                        'all_day' => $config['all_day'] ?? false,
                        'by_appointment' => $config['by_appointment'] ?? false,
                    ],
                );
            }

            // 6. Profile images (only real http(s) sources from the spreadsheet)
            ProfileImage::where('provider_profile_id', $providerProfile->id)->forceDelete();
            $images = $this->parseImages((string) ($record['Image Sources'] ?? ''));
            if (empty($images)) {
                $images = ["https://picsum.photos/seed/profile2-{$i}/400/400"];
            }
            foreach (array_values($images) as $imgIndex => $imageUrl) {
                ProfileImage::create([
                    'user_id' => $user->id,
                    'provider_profile_id' => $providerProfile->id,
                    'image_path' => $imageUrl,
                    'thumbnail_path' => $imageUrl,
                    'is_primary' => $imgIndex === 0,
                ]);
            }
            $user->update(['profile_image' => $images[0]]);

            // 6b. Profile message
            $message = $personalMessage !== '' ? $personalMessage : "Hi there! I'm {$name}.";
            ProfileMessage::updateOrCreate(
                ['provider_profile_id' => $providerProfile->id],
                [
                    'user_id' => $user->id,
                    'message' => $message,
                ],
            );

            // 7. Tours (from the "Touring" column)
            Tour::where('provider_profile_id', $providerProfile->id)->delete();
            $tour = $this->parseTouring((string) ($record['Touring'] ?? ''));
            if ($tour !== null) {
                Tour::create([
                    'provider_profile_id' => $providerProfile->id,
                    'user_id' => $user->id,
                    'city' => $tour,
                    'from' => now(),
                    'to' => now()->addDays(7),
                    'description' => "Currently touring in {$tour}.",
                    'enabled' => true,
                ]);
            }

            // 8. AvailableNow + OnlineUser (keep profiles publicly visible)
            AvailableNow::updateOrCreate(
                ['provider_profile_id' => $providerProfile->id],
                ['user_id' => $user->id, 'status' => 'offline'],
            );

            OnlineUser::updateOrCreate(
                ['provider_profile_id' => $providerProfile->id],
                [
                    'user_id' => $user->id,
                    'status' => 'online',
                    'usage_date' => today(),
                    'usage_count' => 1,
                    'online_started_at' => now(),
                    'online_expires_at' => now()->addMinutes(60),
                ],
            );
        }

        $this->command?->info('Seeded '.count($records).' provider profiles from '.self::DATA_FILE);
    }

    /**
     * Build a [child-slug => id] map for the children of a top-level category.
     *
     * @return array<string, int>
     */
    private function slugMap(string $parentSlug): array
    {
        $parent = Category::query()->where('slug', $parentSlug)->first();

        if ($parent === null) {
            return [];
        }

        return $parent->children()
            ->pluck('id', 'slug')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    /**
     * Parse the "Stats" block into named attributes.
     *
     * @return array<string, string|int|null>
     */
    private function parseStats(string $raw): array
    {
        $result = [
            'age' => null,
            'age_group' => null,
            'ethnicity' => null,
            'hair_color' => null,
            'hair_length' => null,
            'body_type' => null,
            'bust_size' => null,
            'length' => null,
        ];

        foreach (preg_split('/\r?\n/', $raw) ?: [] as $line) {
            if (! str_contains($line, ':')) {
                continue;
            }
            [$key, $value] = array_map('trim', explode(':', $line, 2));
            $key = strtolower($key);

            match ($key) {
                'age group' => $result['age_group'] = $value,
                'ethnicity' => $result['ethnicity'] = $value,
                'hair color' => $result['hair_color'] = $value,
                'hair length' => $result['hair_length'] = $value,
                'body type' => $result['body_type'] = $value,
                'bust size' => $result['bust_size'] = $value,
                'length' => $result['length'] = $value,
                default => null,
            };
        }

        if ($result['age_group'] !== null && preg_match('/(\d{2})/', $result['age_group'], $m) === 1) {
            $result['age'] = (int) $m[1];
        }

        return $result;
    }

    /**
     * Parse the "Social" block for website / onlyfans / twitter links.
     *
     * @return array{website: ?string, onlyfans_username: ?string, twitter_handle: ?string}
     */
    private function parseSocial(string $raw): array
    {
        $result = ['website' => null, 'onlyfans_username' => null, 'twitter_handle' => null];

        foreach (preg_split('/\r?\n/', $raw) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || ! str_contains($line, ':')) {
                continue;
            }
            [$label, $value] = array_map('trim', explode(':', $line, 2));
            $label = strtolower($label);

            if ($label === 'website') {
                $result['website'] = $value;
            } elseif ($label === 'onlyfans') {
                $result['onlyfans_username'] = $this->lastUrlSegment($value);
            } elseif (in_array($label, ['twitter', 'x'], true)) {
                $result['twitter_handle'] = '@'.ltrim($this->lastUrlSegment($value), '@');
            }
        }

        return $result;
    }

    private function lastUrlSegment(string $value): string
    {
        $value = rtrim(trim($value), '/');
        $parts = preg_split('#[/@]#', $value) ?: [];
        $last = end($parts);

        return is_string($last) ? $last : $value;
    }

    /**
     * Map the "Contact For" block to an availability enum value.
     */
    private function parseContactAvailability(string $raw): string
    {
        $raw = strtolower($raw);
        $hasIncall = str_contains($raw, 'incall');
        $hasOutcall = str_contains($raw, 'outcall');

        return match (true) {
            $hasIncall && $hasOutcall => 'incalls-and-outcalls',
            $hasOutcall => 'outcalls-only',
            default => 'incalls-only',
        };
    }

    /**
     * Convert comma separated tags into the matching attribute category ids.
     *
     * @param  array<string, int>  $slugMap
     * @return array<int, int>
     */
    private function mapSlugs(string $raw, array $slugMap): array
    {
        $ids = [];
        foreach (explode(',', $raw) as $tag) {
            $tag = trim($tag);
            if ($tag === '') {
                continue;
            }
            $id = $slugMap[Str::slug($tag)] ?? null;
            if ($id !== null) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * Parse the "Rates" block into rate rows.
     *
     * @return array<int, array{description: string, incall: ?string, outcall: ?string}>
     */
    private function parseRates(string $raw): array
    {
        $rates = [];

        foreach (preg_split('/\r?\n/', $raw) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || stripos($line, 'RATES:') === 0 || ! str_contains($line, ':')) {
                continue;
            }

            [$description, $value] = array_map('trim', explode(':', $line, 2));
            if ($description === '' || $value === '') {
                continue;
            }

            $incall = null;
            $outcall = null;

            if (preg_match('/Incall\s+([^\/]+?)(?:\s*\/|$)/i', $value, $m) === 1) {
                $incall = $this->cleanPrice($m[1]);
            }
            if (preg_match('/Outcall\s+(.+)$/i', $value, $m) === 1) {
                $outcall = $this->cleanPrice($m[1]);
            }
            if ($incall === null && $outcall === null) {
                // Single price line such as "30min Erotic Massage: $200".
                $incall = $this->cleanPrice($value);
            }

            $rates[] = [
                'description' => $description,
                'incall' => $incall,
                'outcall' => $outcall,
            ];
        }

        return $rates;
    }

    private function cleanPrice(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim($value);
        if ($value === '' || $value === '.' || $value === '-') {
            return null;
        }

        return $value;
    }

    /**
     * Extract usable http(s) image urls from the "Image Sources" column.
     *
     * @return array<int, string>
     */
    private function parseImages(string $raw): array
    {
        $images = [];
        foreach (explode(',', $raw) as $candidate) {
            $candidate = trim($candidate);
            if (Str::startsWith($candidate, ['http://', 'https://'])) {
                $images[] = $candidate;
            }
        }

        return array_values(array_unique($images));
    }

    /**
     * Parse the "Touring" column, e.g. "Currently touring in Brisbane (QLD)".
     */
    private function parseTouring(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        if (preg_match('/touring in\s+(.+)$/i', $raw, $m) === 1) {
            return trim($m[1]);
        }

        return $raw;
    }

    /**
     * Parse the "Availability" column into per-weekday config. Only entries
     * that are prefixed with a weekday abbreviation (e.g. "WED24 JUN") are
     * mapped; relative tokens (TODAY/TOMORROW) are ignored.
     *
     * @return array<string, array<string, mixed>>
     */
    private function parseAvailability(string $raw): array
    {
        $byDay = [];

        foreach (preg_split('/\r?\n/', $raw) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || ! str_contains($line, ':')) {
                continue;
            }

            [$label, $value] = array_map('trim', explode(':', $line, 2));
            $abbr = strtoupper(substr($label, 0, 3));
            $day = self::DAY_ABBR[$abbr] ?? null;
            if ($day === null) {
                continue;
            }

            $byDay[$day] = $this->availabilityConfig($value);
        }

        return $byDay;
    }

    /**
     * @return array<string, mixed>
     */
    private function availabilityConfig(string $value): array
    {
        $normalized = strtolower(trim($value));

        if ($normalized === '' || str_contains($normalized, 'unavailable')) {
            return ['enabled' => false];
        }
        if (str_contains($normalized, 'all day')) {
            return ['enabled' => true, 'all_day' => true];
        }
        if (str_contains($normalized, 'appointment')) {
            return ['enabled' => true, 'by_appointment' => true];
        }
        if (preg_match('/(\d{1,2}:\d{2})\s*-\s*(\d{1,2}:\d{2})/', $value, $m) === 1) {
            return ['enabled' => true, 'from_time' => $m[1], 'to_time' => $m[2]];
        }

        return ['enabled' => true];
    }
}
