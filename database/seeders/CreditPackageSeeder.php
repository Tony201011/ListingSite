<?php

namespace Database\Seeders;

use App\Models\CreditPackage;
use Illuminate\Database\Seeder;

class CreditPackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Starter Pack',
                'credits' => 7,
                'price' => 10.00,
                'description' => '7 credits — great for trying out the platform.',
                'status' => 'active',
                'sort_order' => 1,
            ],
            [
                'name' => 'Basic Pack',
                'credits' => 30,
                'price' => 35.00,
                'description' => '30 credits — one month of daily visibility.',
                'status' => 'active',
                'sort_order' => 2,
            ],
            [
                'name' => 'Popular Pack',
                'credits' => 60,
                'price' => 65.00,
                'description' => '60 credits — two months of daily visibility.',
                'status' => 'active',
                'sort_order' => 3,
            ],
            [
                'name' => 'Value Pack',
                'credits' => 120,
                'price' => 120.00,
                'description' => '120 credits — four months of daily visibility.',
                'status' => 'active',
                'sort_order' => 4,
            ],
            [
                'name' => 'Best Value Pack',
                'credits' => 180,
                'price' => 160.00,
                'description' => '180 credits — six months of daily visibility at our best rate.',
                'status' => 'active',
                'sort_order' => 5,
            ],
        ];

        foreach ($packages as $package) {
            CreditPackage::updateOrCreate(
                ['credits' => $package['credits']],
                $package,
            );
        }
    }
}
