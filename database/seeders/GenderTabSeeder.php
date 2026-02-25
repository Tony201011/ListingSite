<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GenderTab;

class GenderTabSeeder extends Seeder
{
    public function run(): void
    {
        $tabs = [
            [
                'label' => 'Female',
                'slug' => 'female',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'label' => 'Male',
                'slug' => 'male',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'label' => 'Transexual',
                'slug' => 'transexual',
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($tabs as $tab) {
            GenderTab::updateOrCreate([
                'slug' => $tab['slug'],
            ], $tab);
        }
    }
}
