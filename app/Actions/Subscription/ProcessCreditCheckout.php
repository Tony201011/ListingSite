<?php

namespace App\Actions\Subscription;

use App\Models\PricingPackage;

class ProcessCreditCheckout
{
    /**
     * @return array{credits: int, price: float, invoice_name: string}
     */
    public function execute(array $validated): array
    {
        $selectedCredits = (int) $validated['credits'];

        $package = PricingPackage::query()
            ->where('credits', $selectedCredits)
            ->where('is_active', true)
            ->first();

        $selectedPrice = $package ? (float) $package->total_price : 0;

        return [
            'credits' => $selectedCredits,
            'price' => $selectedPrice,
            'invoice_name' => $validated['invoice_name'],
        ];
    }
}
