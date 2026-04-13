<?php

namespace Database\Seeders;

use App\Models\GoogleRecaptchaSetting;
use Illuminate\Database\Seeder;

class GoogleRecaptchaSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        GoogleRecaptchaSetting::query()->updateOrCreate(
            ['id' => 1],
            [
                'domain' => 'hotescort.com.au',
                'site_key' => '6LcnfIUsAAAAAHa3kxV60yZGnXUfIKOBjNka25xY',
                'secret_key' => '6LcnfIUsAAAAAHxep_mOtpWNHlkzPTHUj2_MeArw',
                'is_active' => true,
            ],
        );
    }
}
