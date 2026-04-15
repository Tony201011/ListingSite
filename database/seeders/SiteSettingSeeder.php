<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            'meta_key' => 'test,example',
            'meta_description' => 'Test meta description.',
            'enable_cookies' => true,
            'captcha_enabled' => true,
            'cookies_text' => 'We use cookies for analytics and personalization.',
        ];

        if (Schema::hasColumns('site_settings', [
            'fatal_error_page_enabled',
            'fatal_error_default_message',
            'fatal_error_query_param',
        ])) {
            $attributes = array_merge($attributes, [
                'fatal_error_page_enabled' => false,
                'fatal_error_default_message' => 'Site is under maintenance. Please try again shortly.',
                'fatal_error_query_param' => 'fatal_message',
            ]);
        }

        SiteSetting::create($attributes);
    }
}
