<?php

namespace Database\Seeders;

use App\Models\GlobalBanner;
use Illuminate\Database\Seeder;

class GlobalBannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        GlobalBanner::updateOrCreate(
            ['page_key' => 'pricing'],
            [
                'page_keys' => ['pricing'],
                'banner_image_path' => null,
                'banner_title' => 'hotescorts.com.au',
                'banner_subtitle' => 'REAL WOMEN NEAR YOU',
                'is_active' => true,
            ],
        );
    }
}
