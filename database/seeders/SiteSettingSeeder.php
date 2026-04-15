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

        $columns = Schema::getColumnListing('site_settings');

        if (in_array('fatal_error_page_enabled', $columns, true)) {
            $attributes['fatal_error_page_enabled'] = false;
        }

        if (in_array('fatal_error_default_message', $columns, true)) {
            $attributes['fatal_error_default_message'] = 'Site is under maintenance. Please try again shortly.';
        }

        if (in_array('fatal_error_query_param', $columns, true)) {
            $attributes['fatal_error_query_param'] = 'fatal_message';
        }

        SiteSetting::create($attributes);
    }
}
