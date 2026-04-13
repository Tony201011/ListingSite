<?php

namespace Database\Seeders;

use App\Models\Availability;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\ProviderListing;
use App\Models\ProviderProfile;
use App\Models\Rate;
use App\Models\RateGroup;
use App\Models\State;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DummyProviderProfileSeeder extends Seeder
{
    private const TOTAL = 100;

    private const DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    private const RATE_DESCRIPTIONS = ['30 min', '1 hour', '2 hours', 'Overnight'];

    private const INCALL_PRICES = ['$150', '$250', '$400', '$800'];

    private const OUTCALL_PRICES = ['$200', '$300', '$500', '$1000'];

    private const AVAILABILITY_OPTIONS = ['incalls-only', 'outcalls-only', 'incalls-and-outcalls'];

    private const CONTACT_METHOD_OPTIONS = ['phone-only', 'email-contact-form-only-phone-hidden'];

    private const PHONE_PREF_OPTIONS = ['accept-calls-sms', 'accept-calls-only', 'accept-sms-only'];

    private const TIME_WASTER_OPTIONS = ['no', 'yes'];

    private const WEBSITE_TYPES = ['adult', 'porn'];

    private const FEMALE_NAMES = [
        'Amber', 'Bella', 'Chloe', 'Diana', 'Elena', 'Fiona', 'Grace', 'Hannah', 'Iris', 'Jade',
        'Kelly', 'Luna', 'Mia', 'Nina', 'Olivia', 'Petra', 'Quinn', 'Rosa', 'Sophie', 'Tara',
        'Uma', 'Vera', 'Willow', 'Xena', 'Yasmin', 'Zara', 'Alexis', 'Brooke', 'Carmen', 'Daisy',
        'Eva', 'Faith', 'Gina', 'Holly', 'Isla', 'Jasmine', 'Kate', 'Leila', 'Monica', 'Natasha',
        'Opal', 'Paris', 'Ruby', 'Stella', 'Tina', 'Ursula', 'Victoria', 'Wendy', 'Xia', 'Yvette',
    ];

    public function run(): void
    {
        $categoryIds = Category::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->pluck('id')
            ->values()
            ->all();

        $cityRows = City::query()->with('state.country')->get();
        $countries = Country::query()->pluck('id')->values()->all();
        $states = State::query()->pluck('id')->values()->all();
        $cities = $cityRows->pluck('id')->values()->all();

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

        $attributeIds = Category::query()->where('slug', 'attributes')
            ->first()?->children()->pluck('id')->values()->all() ?? [];
        $servicesStyleIds = Category::query()->where('slug', 'services-style')
            ->first()?->children()->pluck('id')->values()->all() ?? [];
        $servicesProvidedIds = Category::query()->where('slug', 'services-you-provide')
            ->first()?->children()->pluck('id')->values()->all() ?? [];

        for ($i = 1; $i <= self::TOTAL; $i++) {
            $email = "provider{$i}@example.com";
            $name = $this->pickName($i);

            // 1. User
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => bcrypt('Provider@12345'),
                    'role' => User::ROLE_PROVIDER,
                    'is_blocked' => false,
                    'mobile' => sprintf('+61 4%02d %03d %03d', $i % 100, ($i * 7) % 1000, ($i * 13) % 1000),
                    'suburb' => $this->pickFrom(['Bondi', 'Surry Hills', 'Newtown', 'Manly', 'Parramatta'], $i),
                    'email_verified_at' => now(),
                    'account_status' => 'active',
                ],
            );

            // 2. ProviderProfile
            $cityId = count($cities) > 0 ? $cities[$i % count($cities)] : null;
            $cityModel = $cityId ? $cityRows->firstWhere('id', $cityId) : null;
            $stateId = $cityModel ? $cityModel->state_id : (count($states) > 0 ? $states[$i % count($states)] : null);
            $countryId = $cityModel ? ($cityModel->state?->country_id ?? null) : (count($countries) > 0 ? $countries[$i % count($countries)] : null);

            $slug = Str::slug($name) . '-' . $i;

            $profileStatuses = ['approved', 'approved', 'approved', 'pending', 'rejected'];

            ProviderProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $name,
                    'slug' => $slug,
                    'age' => rand(21, 45),
                    'description' => "Hi, I'm {$name}. I am a professional and discreet companion offering premium companionship services. I love to meet new people and create unforgettable experiences.",
                    'introduction_line' => "Welcome to my profile! I'm {$name}, your perfect companion.",
                    'profile_text' => "I provide a warm and genuine experience. Available for incalls and outcalls throughout the city. Contact me to arrange an unforgettable time together.",
                    'primary_identity' => count($attributeIds) > 0
                        ? [$this->pickFrom($attributeIds, $i)]
                        : [],
                    'attributes' => count($attributeIds) > 1
                        ? array_slice($attributeIds, $i % max(1, count($attributeIds) - 2), 3)
                        : [],
                    'services_style' => count($servicesStyleIds) > 0
                        ? array_slice($servicesStyleIds, $i % max(1, count($servicesStyleIds) - 3), 4)
                        : [],
                    'services_provided' => count($servicesProvidedIds) > 0
                        ? array_slice($servicesProvidedIds, $i % max(1, count($servicesProvidedIds) - 2), 3)
                        : [],
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
                    'twitter_handle' => '@' . Str::lower(str_replace(' ', '', $name)) . $i,
                    'website' => null,
                    'onlyfans_username' => Str::lower(str_replace(' ', '', $name)) . $i,
                    'country_id' => $countryId,
                    'state_id' => $stateId,
                    'city_id' => $cityId,
                    'latitude' => round(-33.8688 + ($i * 0.01), 7),
                    'longitude' => round(151.2093 + ($i * 0.01), 7),
                    'phone' => sprintf('+61 4%02d %03d %03d', $i % 100, ($i * 7) % 1000, ($i * 13) % 1000),
                    'whatsapp' => sprintf('+61 4%02d %03d %03d', $i % 100, ($i * 11) % 1000, ($i * 17) % 1000),
                    'is_verified' => $i % 3 === 0,
                    'is_featured' => $i <= 10,
                    'profile_status' => $profileStatuses[$i % count($profileStatuses)],
                    'expires_at' => now()->addMonths(rand(1, 12)),
                ],
            );

            // 3. ProviderListing
            $categoryId = count($categoryIds) > 0 ? $categoryIds[$i % count($categoryIds)] : null;
            $thumbnailPath = "provider-listings/dummy-{$i}.svg";
            Storage::disk('public')->put($thumbnailPath, $this->buildDummyThumbnailSvg($i, $name));

            ProviderListing::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'title' => "{$name}'s Live Cam",
                    'age' => rand(21, 45),
                    'category_id' => $categoryId,
                    'website_type' => $this->pickFrom(self::WEBSITE_TYPES, $i),
                    'audience_score' => rand(60, 98),
                    'thumbnail' => $thumbnailPath,
                    'is_live' => $i % 3 === 0,
                    'is_vip' => $i <= 10,
                    'is_active' => true,
                ],
            );

            // 4. RateGroup + Rates
            $rateGroup = RateGroup::updateOrCreate(
                ['user_id' => $user->id, 'name' => 'Standard Rates'],
                [],
            );

            foreach (self::RATE_DESCRIPTIONS as $rateIndex => $description) {
                Rate::updateOrCreate(
                    ['user_id' => $user->id, 'description' => $description],
                    [
                        'incall' => self::INCALL_PRICES[$rateIndex],
                        'outcall' => self::OUTCALL_PRICES[$rateIndex],
                        'extra' => $rateIndex === count(self::RATE_DESCRIPTIONS) - 1
                            ? 'Includes dinner and overnight stay'
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
                    ['user_id' => $user->id, 'day' => $day],
                    [
                        'enabled' => $enabled,
                        'from_time' => ($enabled && ! $allDay) ? '09:00' : null,
                        'to_time' => ($enabled && ! $allDay && ! $tillLate) ? '22:00' : null,
                        'till_late' => $tillLate,
                        'all_day' => $allDay,
                        'by_appointment' => $dayIndex >= 5 && ! $enabled,
                    ],
                );
            }
        }
    }

    private function pickName(int $index): string
    {
        $names = self::FEMALE_NAMES;

        return $names[($index - 1) % count($names)] . ' ' . chr(65 + (int) (($index - 1) / count($names)));
    }

    private function pickFrom(array $items, int $index): mixed
    {
        if (empty($items)) {
            return null;
        }

        return $items[$index % count($items)];
    }

    private function buildDummyThumbnailSvg(int $index, string $name): string
    {
        $colors = [
            ['#1F2937', '#4B5563'],
            ['#7C2D12', '#C2410C'],
            ['#1E3A8A', '#2563EB'],
            ['#14532D', '#16A34A'],
            ['#581C87', '#9333EA'],
            ['#831843', '#DB2777'],
            ['#713F12', '#D97706'],
            ['#134E4A', '#0D9488'],
        ];

        [$start, $end] = $colors[$index % count($colors)];
        $initials = strtoupper(substr($name, 0, 2));

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="512" height="512" viewBox="0 0 512 512">
    <defs>
        <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="{$start}" />
            <stop offset="100%" stop-color="{$end}" />
        </linearGradient>
    </defs>
    <rect width="512" height="512" fill="url(#bg)" rx="24" />
    <text x="50%" y="44%" text-anchor="middle" font-size="120" fill="#FFFFFF" font-family="Arial, sans-serif" font-weight="700">{$initials}</text>
    <text x="50%" y="62%" text-anchor="middle" font-size="44" fill="#E5E7EB" font-family="Arial, sans-serif">Provider #{$index}</text>
</svg>
SVG;
    }
}
