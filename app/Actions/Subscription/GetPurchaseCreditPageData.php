<?php

namespace App\Actions\Subscription;

use App\Models\CreditPackage;
use App\Models\PricingPage;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Auth;

class GetPurchaseCreditPageData
{
    public function execute(): array
    {
        $user = Auth::user();
        $pricingPage = PricingPage::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();

        $packages = CreditPackage::where('status', 'active')
            ->orderBy('sort_order', 'asc')
            ->orderBy('price', 'asc')
            ->get();

        $defaultPackageId = $packages->first()?->id;
        $selectedPackageId = (int) old('package_id', request('package_id', $defaultPackageId));

        if (! $packages->contains('id', $selectedPackageId)) {
            $selectedPackageId = $defaultPackageId;
        }

        $siteSetting = SiteSetting::first();

        return [
            'currentBalance' => $user->credits ?? 0,
            'userName' => $user->name ?? 'User',
            'pricingPage' => $pricingPage,
            'packages' => $packages,
            'selectedPackageId' => $selectedPackageId,
            'stripePublishableKey' => $siteSetting?->stripe_publishable_key,
            'stripeEnabled' => (bool) ($siteSetting?->stripe_enabled && $siteSetting?->stripe_publishable_key && $siteSetting?->stripe_secret_key),
        ];
    }
}
