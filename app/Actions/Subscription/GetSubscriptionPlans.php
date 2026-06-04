<?php

namespace App\Actions\Subscription;

use App\Models\CreditPackage;
use App\Models\PricingPage;

class GetSubscriptionPlans
{
    public function execute(): array
    {
        $pricingPage = PricingPage::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();

        $packages = CreditPackage::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        return [
            'packages' => $packages,
            'pricingPage' => $pricingPage,
        ];
    }
}
