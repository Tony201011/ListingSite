<?php

namespace Database\Seeders;

use App\Models\VerificationExampleImage;
use Illuminate\Database\Seeder;

class VerificationExampleImageSeeder extends Seeder
{
    public function run(): void
    {
        VerificationExampleImage::updateOrCreate(
            ['label' => 'Example 1'],
            [
                'image_url' => 'https://pub-4e37ec8f58e94a569d35a5245489f90d.r2.dev/verification/badge_dummy_image/badge-fummy-image.png',
                'caption' => 'Example 1: clear note + visible face',
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        VerificationExampleImage::updateOrCreate(
            ['label' => 'Example 2'],
            [
                'image_url' => 'https://pub-4e37ec8f58e94a569d35a5245489f90d.r2.dev/verification/badge_dummy_image/same-note-in-another-hand.png',
                'caption' => 'Example 2: same note in other hand',
                'sort_order' => 2,
                'is_active' => true,
            ]
        );
    }
}
