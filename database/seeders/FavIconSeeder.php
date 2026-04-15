<?php

namespace Database\Seeders;

use App\Models\FavIcon;
use Illuminate\Database\Seeder;

class FavIconSeeder extends Seeder
{
    public function run(): void
    {
        FavIcon::query()->update(['is_active' => false]);

        FavIcon::updateOrCreate(
            ['icon_path' => 'favicons/dummy-favicon.png'],
            ['is_active' => true]
        );
    }
}
