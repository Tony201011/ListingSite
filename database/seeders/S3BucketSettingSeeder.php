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
                'region' => 'auto',
                'bucket' => 'hotescort',
                'url' => 'https://cdn.hotescort.com.au',
               // 'url' => 'https://1d992bb4fc68899034857c84336a4603.r2.cloudflarestorage.com/hotescort',
                'endpoint' => 'https://1d992bb4fc68899034857c84336a4603.r2.cloudflarestorage.com',
                'use_path_style_endpoint' => false,
                'is_enabled' => true,
            ],
        );
    }
}
