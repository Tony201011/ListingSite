<?php

namespace App\Http\Controllers\Profile;
use App\Http\Controllers\Controller;

use App\Actions\GetShowHideProfileState;
use App\Actions\UpdateShowHideProfileState;
use App\Http\Requests\UpdateShowHideProfileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ShowHideProfileController extends Controller
{
    public function __construct(
        private GetShowHideProfileState $getShowHideProfileState,
        private UpdateShowHideProfileState $updateShowHideProfileState
    ) {
    }

    public function hideShowProfile(): View
    {
        return view(
            'profile.hide-show',
            $this->getShowHideProfileState->execute(Auth::user())
        );
    }

    public function updateHideShowProfile(UpdateShowHideProfileRequest $request): JsonResponse
    {
        $result = $this->updateShowHideProfileState->execute(
            Auth::user(),
            $request->validated('status')
        );

        return response()->json($result['data'], $result['status']);
    }
}
