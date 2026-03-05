<?php

namespace Database\Seeders;

use App\Models\PricingPackage;
use App\Models\PricingPage;
use Illuminate\Database\Seeder;

class PricingPackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pricingPage = PricingPage::query()->first();

        if (! $pricingPage) {
            return;
        }

        $packages = [
            ['credits' => 7, 'total_price' => '10 AUD $', 'price_per_credit' => 'AUD $1.43', 'sort_order' => 1],
            ['credits' => 30, 'total_price' => '35 AUD $', 'price_per_credit' => 'AUD $1.17', 'sort_order' => 2],
            ['credits' => 60, 'total_price' => '65 AUD $', 'price_per_credit' => 'AUD $1.08', 'sort_order' => 3],
            ['credits' => 120, 'total_price' => '120 AUD $', 'price_per_credit' => 'AUD $1.00', 'sort_order' => 4],
            ['credits' => 180, 'total_price' => '160 AUD $', 'price_per_credit' => 'AUD $0.89', 'sort_order' => 5],
        ];

        foreach ($packages as $package) {
            PricingPackage::updateOrCreate(
                [
                    'pricing_page_id' => $pricingPage->id,
                    'credits' => $package['credits'],
                ],
                [
                    'total_price' => $package['total_price'],
                    'price_per_credit' => $package['price_per_credit'],
                    'sort_order' => $package['sort_order'],
                    'is_active' => true,
                ],
            );
        }
    }
}
