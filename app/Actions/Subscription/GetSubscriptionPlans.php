<?php

namespace App\Actions\Subscription;

use App\Models\PricingPackage;

class GetSubscriptionPlans
{
    public function execute(): array
    {
        $packages = PricingPackage::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return [
            'packages' => $packages,
        ];
    }
}
