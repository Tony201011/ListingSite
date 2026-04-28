<?php

namespace App\Http\Controllers\Profile;

use App\Actions\GetActiveProviderProfile;
use App\Actions\GetProfileMessage;
use App\Actions\SaveProfileMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProfileMessageRequest;
use App\Models\ProviderProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileMessageController extends Controller
{
    public function __construct(
        private GetProfileMessage $getProfileMessage,
        private SaveProfileMessage $saveProfileMessage,
        private GetActiveProviderProfile $getActiveProviderProfile
    ) {}

    public function profileMessage(): View
    {
        $profile = $this->getActiveProviderProfile->execute(Auth::user());

        return view('profile.profile-message', [
            'profileMessage' => $this->getProfileMessage->execute($profile),
        ]);
    }

    public function store(StoreProfileMessageRequest $request): JsonResponse
    {
        $this->authorize('update', ProviderProfile::class);

        $profile = $this->getActiveProviderProfile->execute(Auth::user());

        $result = $this->saveProfileMessage->execute(
            $profile,
            $request->validated('message')
        );

        return response()->json($result->toPayload(), $result->status());
    }
}
