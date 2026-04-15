<?php

namespace Database\Seeders;

use App\Models\Availability;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Postcode;
use App\Models\ProfileImage;
use App\Models\ProfileMessage;
use App\Models\ProviderListing;
use App\Models\ProviderProfile;
use App\Models\Rate;
use App\Models\RateGroup;
use App\Models\State;
use App\Models\Tour;
use App\Models\TourCity;
use App\Models\User;
use App\Models\UserVideo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DummyProviderProfileSeeder extends Seeder
{
    private const TOTAL = 1000;

    private const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    private const RATE_DESCRIPTIONS = ['30 min', '1 hour', '2 hours', 'Overnight'];

    private const INCALL_PRICES = ['$150', '$250', '$400', '$800'];

    private const OUTCALL_PRICES = ['$200', '$300', '$500', '$1000'];

    private const AVAILABILITY_OPTIONS = ['incalls-only', 'outcalls-only', 'incalls-and-outcalls'];

    private const CONTACT_METHOD_OPTIONS = ['phone-only', 'email-contact-form-only-phone-hidden'];

    private const PHONE_PREF_OPTIONS = ['accept-calls-sms', 'accept-calls-only', 'accept-sms-only'];

    private const TIME_WASTER_OPTIONS = ['no', 'yes'];

    private const WEBSITE_TYPES = ['adult', 'porn'];

    private const SAMPLE_VIDEOS = [
        'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
        'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4',
        'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4',
        'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4',
        'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerFun.mp4',
        'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4',
        'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerMeltdowns.mp4',
        'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/Subaru_Outback_with_Kelly_Slater.mp4',
        'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/TearsOfSteel.mp4',
        'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/VolkswagenGTIReview.mp4',
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

        $tourCityNames = TourCity::pluck('name')->values()->all();
        if (empty($tourCityNames)) {
            $tourCityNames = ['Sydney', 'Melbourne', 'Brisbane', 'Perth', 'Adelaide', 'Canberra', 'Hobart', 'Darwin'];
        }

        $attributeIds = Category::query()->where('slug', 'attributes')
            ->first()?->children()->pluck('id')->values()->all() ?? [];
        $servicesStyleIds = Category::query()->where('slug', 'services-style')
            ->first()?->children()->pluck('id')->values()->all() ?? [];
        $servicesProvidedIds = Category::query()->where('slug', 'services-you-provide')
            ->first()?->children()->pluck('id')->values()->all() ?? [];

        $suburbs = Postcode::query()
            ->select(['suburb', 'state', 'postcode'])
            ->inRandomOrder()
            ->limit(500)
            ->get()
            ->map(fn ($row) => "{$row->suburb}, {$row->state} {$row->postcode}")
            ->values()
            ->all();

        // If the postcodes table is empty, read directly from the CSV file used by PostcodeSeeder.
        if (empty($suburbs)) {
            $csvPath = database_path('seeders/2 (2).csv');
            if (file_exists($csvPath) && is_readable($csvPath)) {
                $handle = fopen($csvPath, 'r');
                fgetcsv($handle); // skip header
                $csvSuburbs = [];
                while (($data = fgetcsv($handle)) !== false) {
                    $state = trim($data[0] ?? '');
                    $suburb = trim($data[2] ?? '');
                    $postcode = trim($data[3] ?? '');
                    if ($suburb !== '' && $postcode !== '' && $state !== '') {
                        $csvSuburbs[] = "{$suburb}, {$state} {$postcode}";
                    }
                }
                fclose($handle);
                if (! empty($csvSuburbs)) {
                    shuffle($csvSuburbs);
                    $suburbs = array_slice($csvSuburbs, 0, 500);
                }
            }
        }

        if (empty($suburbs)) {
            $suburbs = [
                // NSW
                'Bondi, NSW 2026',
                'Surry Hills, NSW 2010',
                'Newtown, NSW 2042',
                'Manly, NSW 2095',
                'Parramatta, NSW 2150',
                'Chatswood, NSW 2067',
                'Randwick, NSW 2031',
                'Leichhardt, NSW 2040',
                'Glebe, NSW 2037',
                'Paddington, NSW 2021',
                'Dee Why, NSW 2099',
                'Cronulla, NSW 2230',
                'Penrith, NSW 2750',
                'Blacktown, NSW 2148',
                'Liverpool, NSW 2170',
                'Campbelltown, NSW 2560',
                'Hornsby, NSW 2077',
                'Gosford, NSW 2250',
                'Newcastle, NSW 2300',
                'Wollongong, NSW 2500',
                // VIC
                'Melbourne, VIC 3000',
                'St Kilda, VIC 3182',
                'Richmond, VIC 3121',
                'Fitzroy, VIC 3065',
                'Carlton, VIC 3053',
                'South Yarra, VIC 3141',
                'Prahran, VIC 3181',
                'Collingwood, VIC 3066',
                'Brunswick, VIC 3056',
                'Footscray, VIC 3011',
                'Caulfield, VIC 3162',
                'Brighton, VIC 3186',
                'Geelong, VIC 3220',
                'Ballarat, VIC 3350',
                'Bendigo, VIC 3550',
                'Dandenong, VIC 3175',
                'Frankston, VIC 3199',
                'Box Hill, VIC 3128',
                'Glen Waverley, VIC 3150',
                'Ringwood, VIC 3134',
                // QLD
                'Brisbane City, QLD 4000',
                'South Brisbane, QLD 4101',
                'Fortitude Valley, QLD 4006',
                'Toowong, QLD 4066',
                'Indooroopilly, QLD 4068',
                'Chermside, QLD 4032',
                'Carindale, QLD 4152',
                'Southport, QLD 4215',
                'Surfers Paradise, QLD 4217',
                'Broadbeach, QLD 4218',
                'Robina, QLD 4226',
                'Bundall, QLD 4217',
                'Cairns, QLD 4870',
                'Townsville, QLD 4810',
                'Sunshine Coast, QLD 4558',
                // SA
                'Adelaide, SA 5000',
                'Glenelg, SA 5045',
                'Norwood, SA 5067',
                'Unley, SA 5061',
                'Prospect, SA 5082',
                'Port Adelaide, SA 5015',
                'Marion, SA 5043',
                'Tea Tree Gully, SA 5091',
                'Elizabeth, SA 5112',
                'Mount Gambier, SA 5290',
                // WA
                'Perth, WA 6000',
                'Fremantle, WA 6160',
                'Subiaco, WA 6008',
                'Cottesloe, WA 6011',
                'Victoria Park, WA 6100',
                'Joondalup, WA 6027',
                'Rockingham, WA 6168',
                'Mandurah, WA 6210',
                'Bunbury, WA 6230',
                'Geraldton, WA 6530',
                // ACT
                'Canberra, ACT 2600',
                'Belconnen, ACT 2617',
                'Tuggeranong, ACT 2900',
                'Woden, ACT 2606',
                'Gungahlin, ACT 2912',
                // TAS
                'Hobart, TAS 7000',
                'Sandy Bay, TAS 7005',
                'Launceston, TAS 7250',
                'Devonport, TAS 7310',
                // NT
                'Darwin, NT 0800',
                'Palmerston, NT 0830',
            ];
        }

        for ($i = 1; $i <= self::TOTAL; $i++) {
            $email = "provider{$i}@yopmail.com";
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
                    'suburb' => $this->pickFrom($suburbs, $i),
                    'mobile_verified' => true,
                    'email_verified_at' => now(),
                    'account_status' => 'active',
                ],
            );

            // 2. ProviderProfile
            $cityId = count($cities) > 0 ? $cities[$i % count($cities)] : null;
            $cityModel = $cityId ? $cityRows->firstWhere('id', $cityId) : null;
            $stateId = $cityModel ? $cityModel->state_id : (count($states) > 0 ? $states[$i % count($states)] : null);
            $countryId = $cityModel ? ($cityModel->state?->country_id ?? null) : (count($countries) > 0 ? $countries[$i % count($countries)] : null);

            $slug = Str::slug($name).'-'.$i;

            // 3 out of 5 providers are approved; 1 pending; 1 rejected
            $profileStatuses = ['approved', 'approved', 'approved', 'pending', 'rejected'];

            ProviderProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $name,
                    'slug' => $slug,
                    'age' => rand(21, 45),
                    'description' => "Hi, I'm {$name}. I am a professional and discreet companion offering premium companionship services. I love to meet new people and create unforgettable experiences.",
                    'introduction_line' => "Welcome to my profile! I'm {$name}, your perfect companion.",
                    'profile_text' => 'I provide a warm and genuine experience. Available for incalls and outcalls throughout the city. Contact me to arrange an unforgettable time together.',
                    'primary_identity' => count($attributeIds) > 0
                        ? [$this->pickFrom($attributeIds, $i)]
                        : [],
                    'attributes' => $this->pickMultiple($attributeIds, $i, 3),
                    'services_style' => $this->pickMultiple($servicesStyleIds, $i, 4),
                    'services_provided' => $this->pickMultiple($servicesProvidedIds, $i, 3),
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
            $thumbnailUrl = "https://picsum.photos/seed/listing-{$i}/512/512";

            ProviderListing::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'title' => "{$name}'s Live Cam",
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

            // 6. Profile images (3 per provider; first is primary and also stored as user avatar)
            // Force-delete existing images to avoid the uq_one_primary_per_user constraint on re-runs.
            ProfileImage::where('user_id', $user->id)->forceDelete();

            for ($imgIndex = 1; $imgIndex <= 3; $imgIndex++) {
                $isPrimary = $imgIndex === 1;
                $imageUrl = "https://picsum.photos/seed/profile-{$i}-{$imgIndex}/400/400";
                $thumbUrl = "https://picsum.photos/seed/thumb-{$i}-{$imgIndex}/200/200";

                ProfileImage::create([
                    'user_id' => $user->id,
                    'image_path' => $imageUrl,
                    'thumbnail_path' => $thumbUrl,
                    'is_primary' => $isPrimary,
                ]);
            }

            // Set user profile_image to the primary image URL
            $user->update(['profile_image' => "https://picsum.photos/seed/profile-{$i}-1/400/400"]);

            // 6b. Profile message
            ProfileMessage::updateOrCreate(
                ['user_id' => $user->id],
                ['message' => "Hi there! I'm {$name}. Feel free to send me a message to discuss your requirements or arrange a meeting. I'm responsive and discreet."],
            );

            // 7. User videos (2 per provider)
            for ($vidIndex = 1; $vidIndex <= 2; $vidIndex++) {
                $videoUrl = $this->pickFrom(self::SAMPLE_VIDEOS, $i + $vidIndex);

                UserVideo::updateOrCreate(
                    ['user_id' => $user->id, 'video_path' => $videoUrl],
                    ['original_name' => "video-{$i}-{$vidIndex}.mp4"],
                );
            }

            // 8. Tours (2 per provider)
            for ($tourIndex = 1; $tourIndex <= 2; $tourIndex++) {
                $city = $this->pickFrom($tourCityNames, $i + $tourIndex);
                $from = now()->addDays(($i % 30) + $tourIndex)->setTime(10, 0);
                $to = (clone $from)->addDays(rand(2, 7))->setTime(18, 0);

                Tour::updateOrCreate(
                    ['user_id' => $user->id, 'city' => $city, 'from' => $from],
                    [
                        'to' => $to,
                        'description' => "I will be visiting {$city}. Contact me to arrange a meeting during my stay.",
                        'enabled' => true,
                    ],
                );
            }
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
