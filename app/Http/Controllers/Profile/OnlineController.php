<?php

namespace App\Http\Controllers\Profile;

use App\Actions\GetActiveProviderProfile;
use App\Actions\GetOnlineNowState;
use App\Actions\UpdateOnlineNowStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOnlineStatusRequest;
use App\Models\ProviderProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OnlineController extends Controller
{
    public function __construct(
        private GetOnlineNowState $getOnlineNowState,
        private UpdateOnlineNowStatus $updateOnlineNowStatus,
        private GetActiveProviderProfile $getActiveProviderProfile
    ) {}

    public function onlineNow(): View
    {
        $profile = $this->getActiveProviderProfile->execute(Auth::user());
        $data = $this->getOnlineNowState->execute($profile);

        return view('profile.online-now', $data);
    }

    public function updateStatus(UpdateOnlineStatusRequest $request): JsonResponse
    {
        $this->authorize('update', ProviderProfile::class);

        $profile = $this->getActiveProviderProfile->execute(Auth::user());

        $result = $this->updateOnlineNowStatus->execute(
            $profile,
            $request->validated('status')
        );

        return response()->json($result->toPayload(), $result->status());
    }
}
