<?php

namespace App\Http\Controllers\Profile;

use App\Actions\GetActiveProviderProfile;
use App\Actions\GetAvailableNowState;
use App\Actions\GetShowHideProfileState;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StatusTabsController extends Controller
{
    public function __construct(
        private GetAvailableNowState $getAvailableNowState,
        private GetShowHideProfileState $getShowHideProfileState,
        private GetActiveProviderProfile $getActiveProviderProfile
    ) {}

    public function show(): View
    {
        $profile = $this->getActiveProviderProfile->execute(Auth::user());

        $availableData = $this->getAvailableNowState->execute($profile);
        $visibilityData = $this->getShowHideProfileState->execute($profile);

        return view('profile.status-tabs', [
            'profileId' => $profile?->id,
            'onlineStatus' => $availableData['status'],
            'onlineExpiresAt' => $availableData['expiresAt'],
            'onlineStartedAt' => $availableData['startedAt'],
            'availableStatus' => $availableData['status'],
            'availableExpiresAt' => $availableData['expiresAt'],
            'visibilityStatus' => $visibilityData['status'],
        ]);
    }
}
