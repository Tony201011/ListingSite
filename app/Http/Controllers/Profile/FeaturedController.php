<?php

namespace App\Http\Controllers\Profile;

use App\Actions\GetActiveProviderProfile;
use App\Actions\GetFeaturedState;
use App\Actions\PurchaseFeatured;
use App\Http\Controllers\Controller;
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

        return view('profile.featured', array_merge($data, [
            'userCredits' => $user->credits ?? 0,
        ]));
    }

    public function purchase(Request $request): JsonResponse
    {
        $this->authorize('update', ProviderProfile::class);

        $user = Auth::user();
        $profile = $this->getActiveProviderProfile->execute($user);

        $result = $this->purchaseFeatured->execute($user, $profile);

        return response()->json($result->toPayload(), $result->status());
    }
}
