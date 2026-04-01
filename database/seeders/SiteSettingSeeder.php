<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        SiteSetting::create([
            'meta_key' => 'test,example',
            'meta_description' => 'Test meta description.',
            'enable_cookies' => true,
            'captcha_enabled' => true,
            'cookies_text' => 'We use cookies for analytics and personalization.',
        ]);
    }
}
