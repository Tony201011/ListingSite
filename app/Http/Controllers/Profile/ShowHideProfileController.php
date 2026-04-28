<?php

namespace App\Http\Controllers\Profile;

use App\Actions\GetActiveProviderProfile;
use App\Actions\GetShowHideProfileState;
use App\Actions\UpdateShowHideProfileState;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateShowHideProfileRequest;
use App\Models\ProviderProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ShowHideProfileController extends Controller
{
    public function __construct(
        private GetShowHideProfileState $getShowHideProfileState,
        private UpdateShowHideProfileState $updateShowHideProfileState,
        private GetActiveProviderProfile $getActiveProviderProfile
    ) {}

    public function hideShowProfile(): View
    {
        $profile = $this->getActiveProviderProfile->execute(Auth::user());

        return view(
            'profile.hide-show',
            $this->getShowHideProfileState->execute($profile)
        );
    }

    public function updateHideShowProfile(UpdateShowHideProfileRequest $request): JsonResponse
    {
        $this->authorize('update', ProviderProfile::class);

        $profile = $this->getActiveProviderProfile->execute(Auth::user());

        $result = $this->updateShowHideProfileState->execute(
            $profile,
            $request->validated('status')
        );

        return response()->json($result->toPayload(), $result->status());
    }
}
