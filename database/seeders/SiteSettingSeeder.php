<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiteSetting;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        SiteSetting::create([
            'meta_key' => 'test,example',
            'meta_description' => 'Test meta description.',
            'enable_cookies' => true,
            'cookies_text' => 'We use cookies for analytics and personalization.'
        ]);
    }
}
