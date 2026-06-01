<?php

namespace App\Http\Controllers\Profile;

use App\Actions\GetActiveProviderProfile;
use App\Actions\GetAvailableNowState;
use App\Actions\UpdateAvailableNowStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOnlineStatusRequest;
use App\Models\ProviderProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OnlineController extends Controller
{
    public function __construct(
        private GetAvailableNowState $getAvailableNowState,
        private UpdateAvailableNowStatus $updateAvailableNowStatus,
        private GetActiveProviderProfile $getActiveProviderProfile
    ) {}

    public function onlineNow(): View
    {
        $profile = $this->getActiveProviderProfile->execute(Auth::user());
        $data = $this->getAvailableNowState->execute($profile);
        $data['onlineStatus'] = (bool) ($data['status'] ?? false);
        $data['onlineStartedAt'] = $data['startedAt'] ?? null;
        $data['profileId'] = $profile?->id;

        return view('profile.online-now', $data);
    }

    public function updateStatus(UpdateOnlineStatusRequest $request): JsonResponse
    {
        $this->authorize('update', ProviderProfile::class);

        $profile = $this->getActiveProviderProfile->execute(Auth::user());

        $result = $this->updateAvailableNowStatus->execute(
            $profile,
            $request->validated('status')
        );

        return response()->json($result->toPayload(), $result->status());
    }
}
