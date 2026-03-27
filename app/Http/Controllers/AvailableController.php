<?php

namespace App\Http\Controllers;

use App\Actions\GetAvailableNowState;
use App\Actions\UpdateAvailableNowStatus;
use App\Http\Requests\UpdateAvailableStatusRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AvailableController extends Controller
{
    public function __construct(
        private GetAvailableNowState $getAvailableNowState,
        private UpdateAvailableNowStatus $updateAvailableNowStatus
    ) {
    }

    public function availableNow(): View
    {
        $data = $this->getAvailableNowState->execute(auth()->user());

        return view('profile.available-now', $data);
    }

    public function availableUpdateStatus(UpdateAvailableStatusRequest $request): JsonResponse
    {
        $result = $this->updateAvailableNowStatus->execute(
            $request->user(),
            $request->string('status')->toString()
        );

        return response()->json($result['data'], $result['code']);
    }
}
