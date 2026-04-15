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
            'fatal_error_page_enabled' => false,
            'fatal_error_default_message' => 'Site is under maintenance. Please try again shortly.',
            'fatal_error_query_param' => 'fatal_message',
        ]);
    }
}
