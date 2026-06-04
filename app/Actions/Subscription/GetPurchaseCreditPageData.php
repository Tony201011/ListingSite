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
        $profile = $this->getActiveProviderProfile->execute($user);
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
        $lockedPackageId = request()->boolean('lock_package') && $selectedPackage
            ? $selectedPackage->id
            : null;

        $paymentProvider = $this->paymentProviderManager->current();
        $currentBalance = $profile ? $this->walletLedgerService->currentBalance($profile) : 0;

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
        ];
    }
}
