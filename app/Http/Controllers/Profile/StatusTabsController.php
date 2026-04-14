<?php

namespace App\Http\Controllers\Profile;

use App\Actions\GetAvailableNowState;
use App\Actions\GetOnlineNowState;
use App\Actions\GetShowHideProfileState;
use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StatusTabsController extends Controller
{
    public function __construct(
        private GetOnlineNowState $getOnlineNowState,
        private GetAvailableNowState $getAvailableNowState,
        private GetShowHideProfileState $getShowHideProfileState
    ) {}

    public function show(): View
    {
        $user = Auth::user();

        $onlineData = $this->getOnlineNowState->execute($user);
        $availableData = $this->getAvailableNowState->execute($user);
        $visibilityData = $this->getShowHideProfileState->execute($user);
        $statusSettings = SiteSetting::getStatusSettings();

        return view('profile.status-tabs', [
            'onlineStatus' => $onlineData['onlineStatus'],
            'onlineRemainingUses' => $onlineData['remainingUses'],
            'onlineExpiresAt' => $onlineData['expiresAt'],
            'availableStatus' => $availableData['status'],
            'availableRemainingUses' => $availableData['remainingUses'],
            'availableExpiresAt' => $availableData['expiresAt'],
            'visibilityStatus' => $visibilityData['status'],
            'onlineMaxUses' => $statusSettings['online_status_max_uses'],
            'onlineDurationMinutes' => $statusSettings['online_status_duration_minutes'],
            'availableMaxUses' => $statusSettings['available_now_max_uses'],
            'availableDurationMinutes' => $statusSettings['available_now_duration_minutes'],
        ]);
    }
}
