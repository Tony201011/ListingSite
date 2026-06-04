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
                'bonus_credits' => 0,
                'price' => 10.00,
                'currency' => 'AUD',
                'description' => '7 credits — great for trying out the platform.',
                'status' => 'active',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Basic Pack',
                'credits' => 30,
                'bonus_credits' => 0,
                'price' => 35.00,
                'currency' => 'AUD',
                'description' => '30 credits — one month of daily visibility.',
                'status' => 'active',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Popular Pack',
                'credits' => 60,
                'bonus_credits' => 0,
                'price' => 65.00,
                'currency' => 'AUD',
                'description' => '60 credits — two months of daily visibility.',
                'status' => 'active',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Value Pack',
                'credits' => 120,
                'bonus_credits' => 0,
                'price' => 120.00,
                'currency' => 'AUD',
                'description' => '120 credits — four months of daily visibility.',
                'status' => 'active',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Best Value Pack',
                'credits' => 180,
                'bonus_credits' => 0,
                'price' => 160.00,
                'currency' => 'AUD',
                'description' => '180 credits — six months of daily visibility at our best rate.',
                'status' => 'active',
                'is_active' => true,
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
