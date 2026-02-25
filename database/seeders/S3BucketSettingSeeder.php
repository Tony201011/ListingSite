<?php

namespace Database\Seeders;

use App\Models\S3BucketSetting;
use Illuminate\Database\Seeder;

class S3BucketSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        S3BucketSetting::query()->updateOrCreate(
            ['id' => 1],
            [
                'key' => null,
                'secret' => null,
                'region' => null,
                'bucket' => null,
                'url' => null,
                'endpoint' => null,
                'use_path_style_endpoint' => false,
                'is_enabled' => false,
            ],
        );
    }
}