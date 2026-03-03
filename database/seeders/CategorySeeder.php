<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'All Live Cams',
            'Anal',
            'Asian',
            'ASMR',
            'Ball busting',
            'BBW',
            'BDSM',
            'Big Tits',
            'Black Hair',
            'Blonde',
            'Brunette',
            'CBT',
            'Chastity training',
            'Cosplay',
            'Couple',
            'Cuckolding',
        ];

        foreach ($categories as $index => $name) {
            Category::updateOrCreate(
                [
                    'slug' => Str::slug($name),
                ],
                [
                    'name' => $name,
                    'website_type' => 'adult',
                    'sort_order' => $index,
                    'is_active' => true,
                ],
            );
        }

        $primaryIdentity = Category::updateOrCreate(
            [
                'slug' => 'primary-identity',
            ],
            [
                'parent_id' => null,
                'name' => 'Primary identity',
                'website_type' => 'adult',
                'sort_order' => 100,
                'is_active' => true,
            ],
        );

        $primaryIdentityChildren = [
            'milf',
            'girl next door',
            'courage',
            'trans',
            'sympho',
            'sex goddess',
            'naughty housewife',
            'pornstar',
            'kinky lady',
            'elite courtesan',
        ];

        foreach ($primaryIdentityChildren as $index => $name) {
            Category::updateOrCreate(
                [
                    'slug' => Str::slug($name),
                ],
                [
                    'parent_id' => $primaryIdentity->id,
                    'name' => Str::title($name),
                    'website_type' => 'adult',
                    'sort_order' => 101 + $index,
                    'is_active' => true,
                ],
            );
        }

        $attributes = Category::updateOrCreate(
            [
                'slug' => 'attributes',
            ],
            [
                'parent_id' => null,
                'name' => 'Attributes',
                'website_type' => 'adult',
                'sort_order' => 200,
                'is_active' => true,
            ],
        );

        $attributeChildren = [
            'heterosexual',
            'bisexual',
            'high end trans only',
            'cheap trans available',
            'natural boobs',
            'enhanced boobs',
            'covered in tattoos',
            'some tattoos',
            'no tattoos',
            'lingerie piercing',
            'clit piercing',
            'body piercings',
            'long legs',
            'curly hair',
            'big boobs',
            'round bottom',
            'natural bush',
            'well groomed',
            'fully shaved or waxed',
            'anal ok',
            'no anal',
            'fair skin',
            'tanned skin',
            'asian skin',
            'dark skin',
            'quickies',
            'no quickies',
            'non smoker',
            'covid vaccinated',
        ];

        foreach ($attributeChildren as $index => $name) {
            Category::updateOrCreate(
                [
                    'slug' => Str::slug($name),
                ],
                [
                    'parent_id' => $attributes->id,
                    'name' => Str::title($name),
                    'website_type' => 'adult',
                    'sort_order' => 201 + $index,
                    'is_active' => true,
                ],
            );
        }

        $servicesStyle = Category::updateOrCreate(
            [
                'slug' => 'services-style',
            ],
            [
                'parent_id' => null,
                'name' => 'Services & style',
                'website_type' => 'adult',
                'sort_order' => 300,
                'is_active' => true,
            ],
        );

        $servicesStyleChildren = [
            'outfit requests welcome',
            'lingerie',
            'high heels',
            'thigh high boots',
            'pegging',
            'pregnant',
            'classy',
            'love conversations',
            'shower facilities',
            'wicked wall',
            'squirt',
            'party kick',
            'groupie kick',
            'stripper',
            'touring escort',
            'published pornstar',
            'model',
            'sexual experience',
            'french kissing',
            'no kissing',
            'toys',
            'no rough sex',
            'rough sex ok',
            'spanking',
            'fantasy experiences',
            'school girl fantasy',
            'secretary fantasy',
            'nurse fantasy',
        ];

        foreach ($servicesStyleChildren as $index => $name) {
            Category::updateOrCreate(
                [
                    'slug' => Str::slug($name),
                ],
                [
                    'parent_id' => $servicesStyle->id,
                    'name' => Str::title($name),
                    'website_type' => 'adult',
                    'sort_order' => 301 + $index,
                    'is_active' => true,
                ],
            );
        }

        $servicesProvide = Category::updateOrCreate(
            [
                'slug' => 'services-you-provide',
            ],
            [
                'parent_id' => null,
                'name' => 'Services you provide',
                'website_type' => 'adult',
                'sort_order' => 400,
                'is_active' => true,
            ],
        );

        $servicesProvideChildren = [
            'standard service',
            'gfe',
            'pse (or very naughty girlfriend)',
            'fantasy / roleplay / kinky fetishes',
            'erotic body rubs',
            'social, netflix or dinner dates',
            'overnight services',
            'fly me to you',
            'submission / dom sessions',
            'dominatrix / dom sessions',
            'escort for couples',
            'threesome bookings with another sw',
            'swingers party companion',
            'online services',
        ];

        foreach ($servicesProvideChildren as $index => $name) {
            Category::updateOrCreate(
                [
                    'slug' => Str::slug($name),
                ],
                [
                    'parent_id' => $servicesProvide->id,
                    'name' => Str::title($name),
                    'website_type' => 'adult',
                    'sort_order' => 401 + $index,
                    'is_active' => true,
                ],
            );
        }

        $ageGroup = Category::updateOrCreate(
            [
                'slug' => 'age-group',
            ],
            [
                'parent_id' => null,
                'name' => 'Age Group',
                'website_type' => 'adult',
                'sort_order' => 500,
                'is_active' => true,
            ],
        );

        $ageGroupChildren = [
            ['name' => '18-24', 'slug' => '18-24'],
            ['name' => '25-30', 'slug' => '25-30'],
            ['name' => '31-35', 'slug' => '31-35'],
            ['name' => '36-40', 'slug' => '36-40'],
            ['name' => '40+', 'slug' => '40-plus'],
        ];

        foreach ($ageGroupChildren as $index => $item) {
            Category::updateOrCreate(
                [
                    'slug' => $item['slug'],
                ],
                [
                    'parent_id' => $ageGroup->id,
                    'name' => $item['name'],
                    'website_type' => 'adult',
                    'sort_order' => 501 + $index,
                    'is_active' => true,
                ],
            );
        }

        $hairColor = Category::updateOrCreate(
            [
                'slug' => 'hair-color',
            ],
            [
                'parent_id' => null,
                'name' => 'Hair color',
                'website_type' => 'adult',
                'sort_order' => 600,
                'is_active' => true,
            ],
        );

        $hairColorChildren = [
            'Blonde',
            'Brunette',
            'Redhead',
            'Black',
            'Brown',
        ];

        foreach ($hairColorChildren as $index => $name) {
            Category::updateOrCreate(
                [
                    'slug' => Str::slug($name),
                ],
                [
                    'parent_id' => $hairColor->id,
                    'name' => $name,
                    'website_type' => 'adult',
                    'sort_order' => 601 + $index,
                    'is_active' => true,
                ],
            );
        }

        $hairLength = Category::updateOrCreate(
            [
                'slug' => 'hair-length',
            ],
            [
                'parent_id' => null,
                'name' => 'Hair length',
                'website_type' => 'adult',
                'sort_order' => 700,
                'is_active' => true,
            ],
        );

        $hairLengthChildren = [
            'Short',
            'Medium',
            'Long',
            'Very Long',
        ];

        foreach ($hairLengthChildren as $index => $name) {
            Category::updateOrCreate(
                [
                    'slug' => Str::slug($name),
                ],
                [
                    'parent_id' => $hairLength->id,
                    'name' => $name,
                    'website_type' => 'adult',
                    'sort_order' => 701 + $index,
                    'is_active' => true,
                ],
            );
        }

        $ethnicity = Category::updateOrCreate(
            [
                'slug' => 'ethnicity',
            ],
            [
                'parent_id' => null,
                'name' => 'Ethnicity',
                'website_type' => 'adult',
                'sort_order' => 800,
                'is_active' => true,
            ],
        );

        $ethnicityChildren = [
            'Caucasian',
            'Asian',
            'Indian',
            'Middle Eastern',
            'Hispanic',
        ];

        foreach ($ethnicityChildren as $index => $name) {
            Category::updateOrCreate(
                [
                    'slug' => Str::slug($name),
                ],
                [
                    'parent_id' => $ethnicity->id,
                    'name' => $name,
                    'website_type' => 'adult',
                    'sort_order' => 801 + $index,
                    'is_active' => true,
                ],
            );
        }

        $bodyType = Category::updateOrCreate(
            [
                'slug' => 'body-type',
            ],
            [
                'parent_id' => null,
                'name' => 'Body type',
                'website_type' => 'adult',
                'sort_order' => 900,
                'is_active' => true,
            ],
        );

        $bodyTypeChildren = [
            'Slender',
            'Average',
            'Athletic',
            'Curvy',
            'Full Figured',
        ];

        foreach ($bodyTypeChildren as $index => $name) {
            Category::updateOrCreate(
                [
                    'slug' => Str::slug($name),
                ],
                [
                    'parent_id' => $bodyType->id,
                    'name' => $name,
                    'website_type' => 'adult',
                    'sort_order' => 901 + $index,
                    'is_active' => true,
                ],
            );
        }

        $bustSize = Category::updateOrCreate(
            [
                'slug' => 'bust-size',
            ],
            [
                'parent_id' => null,
                'name' => 'Bust size',
                'website_type' => 'adult',
                'sort_order' => 1000,
                'is_active' => true,
            ],
        );

        $bustSizeChildren = [
            ['name' => 'A cup', 'slug' => 'a-cup'],
            ['name' => 'B cup', 'slug' => 'b-cup'],
            ['name' => 'C cup', 'slug' => 'c-cup'],
            ['name' => 'D cup', 'slug' => 'd-cup'],
            ['name' => 'DD+', 'slug' => 'dd-plus'],
        ];

        foreach ($bustSizeChildren as $index => $item) {
            Category::updateOrCreate(
                [
                    'slug' => $item['slug'],
                ],
                [
                    'parent_id' => $bustSize->id,
                    'name' => $item['name'],
                    'website_type' => 'adult',
                    'sort_order' => 1001 + $index,
                    'is_active' => true,
                ],
            );
        }

        $yourLength = Category::updateOrCreate(
            [
                'slug' => 'your-length',
            ],
            [
                'parent_id' => null,
                'name' => 'Your length',
                'website_type' => 'adult',
                'sort_order' => 1100,
                'is_active' => true,
            ],
        );

        $yourLengthChildren = [
            ['name' => 'Under 5\'0"', 'slug' => 'under-5-0'],
            ['name' => '5\'0" - 5\'3"', 'slug' => '5-0-5-3'],
            ['name' => '5\'4" - 5\'6"', 'slug' => '5-4-5-6'],
            ['name' => '5\'7" - 5\'9"', 'slug' => '5-7-5-9'],
            ['name' => '5\'10" and above', 'slug' => '5-10-and-above'],
        ];

        foreach ($yourLengthChildren as $index => $item) {
            Category::updateOrCreate(
                [
                    'slug' => $item['slug'],
                ],
                [
                    'parent_id' => $yourLength->id,
                    'name' => $item['name'],
                    'website_type' => 'adult',
                    'sort_order' => 1101 + $index,
                    'is_active' => true,
                ],
            );
        }

        $availability = Category::updateOrCreate(
            [
                'slug' => 'availability',
            ],
            [
                'parent_id' => null,
                'name' => 'Are you available for',
                'website_type' => 'adult',
                'sort_order' => 1200,
                'is_active' => true,
            ],
        );

        $availabilityChildren = [
            'Incalls only',
            'Outcalls only',
            'Incalls and Outcalls',
        ];

        foreach ($availabilityChildren as $index => $name) {
            Category::updateOrCreate(
                [
                    'slug' => Str::slug($name),
                ],
                [
                    'parent_id' => $availability->id,
                    'name' => $name,
                    'website_type' => 'adult',
                    'sort_order' => 1201 + $index,
                    'is_active' => true,
                ],
            );
        }

        $contactMethod = Category::updateOrCreate(
            [
                'slug' => 'contact-method',
            ],
            [
                'parent_id' => null,
                'name' => 'How can people contact you?',
                'website_type' => 'adult',
                'sort_order' => 1300,
                'is_active' => true,
            ],
        );

        $contactMethodChildren = [
            'Phone only',
            'Email contact form only (phone hidden)',
        ];

        foreach ($contactMethodChildren as $index => $name) {
            Category::updateOrCreate(
                [
                    'slug' => Str::slug($name),
                ],
                [
                    'parent_id' => $contactMethod->id,
                    'name' => $name,
                    'website_type' => 'adult',
                    'sort_order' => 1301 + $index,
                    'is_active' => true,
                ],
            );
        }

        $phoneContactPreferences = Category::updateOrCreate(
            [
                'slug' => 'phone-contact-preferences',
            ],
            [
                'parent_id' => null,
                'name' => 'Phone contact preferences',
                'website_type' => 'adult',
                'sort_order' => 1400,
                'is_active' => true,
            ],
        );

        $phoneContactPreferenceChildren = [
            'Accept calls & SMS',
            'Accept calls only',
            'Accept SMS only',
        ];

        foreach ($phoneContactPreferenceChildren as $index => $name) {
            Category::updateOrCreate(
                [
                    'slug' => Str::slug($name),
                ],
                [
                    'parent_id' => $phoneContactPreferences->id,
                    'name' => $name,
                    'website_type' => 'adult',
                    'sort_order' => 1401 + $index,
                    'is_active' => true,
                ],
            );
        }

        $timeWasterShield = Category::updateOrCreate(
            [
                'slug' => 'time-waster-shield',
            ],
            [
                'parent_id' => null,
                'name' => 'Use time waster shield for SMS?',
                'website_type' => 'adult',
                'sort_order' => 1500,
                'is_active' => true,
            ],
        );

        $timeWasterShieldChildren = [
            'No',
            'Yes',
        ];

        foreach ($timeWasterShieldChildren as $index => $name) {
            Category::updateOrCreate(
                [
                    'slug' => Str::slug($name),
                ],
                [
                    'parent_id' => $timeWasterShield->id,
                    'name' => $name,
                    'website_type' => 'adult',
                    'sort_order' => 1501 + $index,
                    'is_active' => true,
                ],
            );
        }
    }
}
