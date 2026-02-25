<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'All Live Cams',
            'Anal',
            'Asian',
            'ASMR',
            'Ball busting',
            'BBW',
            'BDSM',
            'Big Tits',
            'Black Hair',
            'Blonde',
            'Brunette',
            'CBT',
            'Chastity training',
            'Cosplay',
            'Couple',
            'Cuckolding',
        ];

        foreach ($categories as $index => $name) {
            Category::updateOrCreate(
                [
                    'slug' => Str::slug($name),
                ],
                [
                    'name' => $name,
                    'website_type' => 'adult',
                    'sort_order' => $index,
                    'is_active' => true,
                ],
            );
        }
    }
}
