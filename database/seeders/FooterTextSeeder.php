<?php

namespace Database\Seeders;

use App\Models\FooterText;
use Illuminate\Database\Seeder;

class FooterTextSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FooterText::updateOrCreate(
            ['is_active' => true],
            [
                'copyright_text' => '© {year} Hotescorts Directory. All rights reserved.',
                'disclaimer_text' => 'This platform is for adults only (18+) and provides advertising listings only.',
                'is_active' => true,
            ],
        );
    }
}
