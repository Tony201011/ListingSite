<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CookieSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('cookie_settings')->insertOrIgnore([
            [
                'name' => 'Essential Cookies',
                'description' => 'These cookies are necessary for the website to function and cannot be switched off.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Analytics Cookies',
                'description' => 'These cookies help us understand how visitors interact with the website.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Marketing Cookies',
                'description' => 'These cookies are used to deliver advertisements relevant to you.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
