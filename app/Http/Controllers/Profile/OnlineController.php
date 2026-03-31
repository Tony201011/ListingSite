<?php

namespace App\Http\Controllers\Profile;
use App\Http\Controllers\Controller;
use App\Actions\GetOnlineNowState;
use App\Actions\UpdateOnlineNowStatus;
use App\Http\Requests\UpdateOnlineStatusRequest;
use App\Models\ProviderProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OnlineController extends Controller
{
    public function __construct(
        private GetOnlineNowState $getOnlineNowState,
        private UpdateOnlineNowStatus $updateOnlineNowStatus
    ) {
    }

    public function onlineNow(): View
    {
        $data = $this->getOnlineNowState->execute(auth::user());

        return view('profile.online-now', $data);
    }

    public function onlineUpdateStatus(UpdateOnlineStatusRequest $request): JsonResponse
    {
        $this->authorize('update', ProviderProfile::class);

        $result = $this->updateOnlineNowStatus->execute(
            auth::user(),
            $request->validated('status')
        );

        return response()->json($result['data'], $result['status']);
    }
}
