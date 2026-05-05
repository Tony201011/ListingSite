<?php

namespace App\Actions\Subscription;

use App\Models\CreditPackage;
use Illuminate\Support\Facades\Auth;

class GetPurchaseCreditPageData
{
    public function execute(): array
    {
        $user = Auth::user();

        $packages = CreditPackage::where('status', 'active')
            ->orderBy('sort_order', 'asc')
            ->orderBy('price', 'asc')
            ->get();

        $defaultPackageId = $packages->first()?->id;
        $selectedPackageId = (int) old('package_id', request('package_id', $defaultPackageId));

        if (! $packages->contains('id', $selectedPackageId)) {
            $selectedPackageId = $defaultPackageId;
        }

        return [
            'currentBalance' => $user->credits ?? 0,
            'userName' => $user->name ?? 'User',
            'packages' => $packages,
            'selectedPackageId' => $selectedPackageId,
        ];
    }
}
