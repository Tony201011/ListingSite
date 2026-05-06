<?php

namespace App\Actions\Subscription;

use App\Models\CreditPackage;

class GetSubscriptionPlans
{
    public function execute(): array
    {
        $packages = CreditPackage::query()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        return [
            'packages' => $packages,
        ];
    }
}
