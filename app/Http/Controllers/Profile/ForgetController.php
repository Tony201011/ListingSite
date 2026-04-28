<?php

namespace App\Http\Controllers\Profile;

use App\Actions\GetActiveProviderProfile;
use App\Actions\GetSetAndForgetState;
use App\Actions\SaveSetAndForget;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveSetAndForgetRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ForgetController extends Controller
{
    public function __construct(
        private GetSetAndForgetState $getSetAndForgetState,
        private SaveSetAndForget $saveSetAndForget,
        private GetActiveProviderProfile $getActiveProviderProfile
    ) {}

    public function setForget(): View
    {
        $profile = $this->getActiveProviderProfile->execute(Auth::user());
        $data = $this->getSetAndForgetState->execute($profile);

        return view('profile.set-forget', $data);
    }

    public function save(SaveSetAndForgetRequest $request): JsonResponse
    {
        $profile = $this->getActiveProviderProfile->execute(Auth::user());

        $result = $this->saveSetAndForget->execute(
            $profile,
            $request->validated()
        );

        return response()->json($result->toPayload(), $result->status());
    }
}
