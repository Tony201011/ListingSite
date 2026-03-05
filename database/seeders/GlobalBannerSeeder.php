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
                'banner_image_path' => null,
                'is_active' => true,
            ],
        );
    }
}
