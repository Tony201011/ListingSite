<?php

namespace App\Http\Controllers\Profile;

use App\Actions\GetActiveProviderProfile;
use App\Actions\GetAvailableNowState;
use App\Actions\UpdateAvailableNowStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAvailableStatusRequest;
use App\Models\ProviderProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AvailableController extends Controller
{
    public function __construct(
        private GetAvailableNowState $getAvailableNowState,
        private UpdateAvailableNowStatus $updateAvailableNowStatus,
        private GetActiveProviderProfile $getActiveProviderProfile
    ) {}

    public function availableNow(): View
    {
        $profile = $this->getActiveProviderProfile->execute(Auth::user());
        $data = $this->getAvailableNowState->execute($profile);

        return view('profile.available-now', $data);
    }

    public function updateStatus(UpdateAvailableStatusRequest $request): JsonResponse
    {
        $this->authorize('update', ProviderProfile::class);

        $profile = $this->getActiveProviderProfile->execute(Auth::user());

        $result = $this->updateAvailableNowStatus->execute(
            $profile,
            $request->string('status')->toString()
        );

        return response()->json($result->toPayload(), $result->status());
    }
}
