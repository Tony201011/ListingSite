<?php

namespace App\Http\Controllers\Profile;

use App\Actions\GetActiveProviderProfile;
use App\Actions\GetFeaturedState;
use App\Actions\PurchaseFeatured;
use App\Http\Controllers\Controller;
use App\Models\CreditPackage;
use App\Models\ProviderProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FeaturedController extends Controller
{
    public function __construct(
        private GetFeaturedState $getFeaturedState,
        private PurchaseFeatured $purchaseFeatured,
        private GetActiveProviderProfile $getActiveProviderProfile
    ) {}

    public function featured(): View
    {
        $user = Auth::user();
        $profile = $this->getActiveProviderProfile->execute($user);
        $data = $this->getFeaturedState->execute($profile);

        $creditPackages = CreditPackage::where('status', 'active')
            ->orderBy('sort_order', 'asc')
            ->orderBy('price', 'asc')
            ->get(['id', 'name', 'credits', 'price', 'description']);

        return view('profile.featured', array_merge($data, [
            'userCredits' => $user->credits ?? 0,
            'creditPackages' => $creditPackages,
        ]));
    }

    public function purchase(Request $request): JsonResponse
    {
        $this->authorize('update', ProviderProfile::class);

        $validated = $request->validate([
            'tier' => ['nullable', 'string', 'in:'.implode(',', PurchaseFeatured::TIERS)],
            'days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        $tier = $validated['tier'] ?? PurchaseFeatured::TIER_NORMAL;
        $days = (int) ($validated['days'] ?? 1);

        $user = Auth::user();
        $profile = $this->getActiveProviderProfile->execute($user);

        $result = $this->purchaseFeatured->execute($user, $profile, $tier, $days);

        return response()->json($result->toPayload(), $result->status());
    }
}
