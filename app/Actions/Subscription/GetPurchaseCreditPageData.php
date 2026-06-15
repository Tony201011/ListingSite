<?php

namespace App\Actions\Subscription;

use App\Actions\GetActiveProviderProfile;
use App\Models\CreditPackage;
use App\Models\PricingPage;
use App\Services\Payments\PaymentProviderManager;
use App\Services\WalletLedgerService;
use Illuminate\Support\Facades\Auth;

class GetPurchaseCreditPageData
{
    public function __construct(
        private GetActiveProviderProfile $getActiveProviderProfile,
        private PaymentProviderManager $paymentProviderManager,
        private WalletLedgerService $walletLedgerService,
    ) {}

    public function execute(): array
    {
        $user = Auth::user();

        $pricingPage = PricingPage::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();

        $packages = CreditPackage::query()
            ->active()
            ->orderBy('sort_order', 'asc')
            ->orderBy('price', 'asc')
            ->get();

        $defaultPackageId = $packages->first()?->id;
        $selectedPackageId = (int) old('package_id', request('package_id', $defaultPackageId));

        if (! $packages->contains('id', $selectedPackageId)) {
            $selectedPackageId = $defaultPackageId;
        }

        $selectedPackage = $packages->firstWhere('id', $selectedPackageId);
        $paymentProvider = $this->paymentProviderManager->current();

        // Guest mode: no authenticated user — show packages for preview only
        if (! $user) {
            return [
                'currentBalance' => 0,
                'userName' => 'Guest',
                'activeProfile' => null,
                'pricingPage' => $pricingPage,
                'packages' => $packages,
                'selectedPackageId' => $selectedPackageId,
                'selectedPackage' => $selectedPackage,
                'lockedPackageId' => null,
                'paymentProvider' => $paymentProvider->name(),
                'paymentPublicKey' => null,
                'paymentEnabled' => false,
                'guestMode' => true,
            ];
        }

        $profile = $this->getActiveProviderProfile->execute($user);
        $currentBalance = $profile ? $this->walletLedgerService->currentBalance($profile) : 0;

        $lockedPackageId = request()->boolean('lock_package') && $selectedPackage
            ? $selectedPackage->id
            : null;

        return [
            'currentBalance' => $currentBalance,
            'userName' => $user->name ?? 'User',
            'activeProfile' => $profile,
            'pricingPage' => $pricingPage,
            'packages' => $packages,
            'selectedPackageId' => $selectedPackageId,
            'selectedPackage' => $selectedPackage,
            'lockedPackageId' => $lockedPackageId,
            'paymentProvider' => $paymentProvider->name(),
            'paymentPublicKey' => $paymentProvider->publicKey(),
            'paymentEnabled' => $paymentProvider->isConfigured() && filled($paymentProvider->publicKey()),
            'guestMode' => false,
        ];
    }
}
