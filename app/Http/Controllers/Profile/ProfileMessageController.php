<?php

namespace App\Http\Controllers\Profile;
use App\Http\Controllers\Controller;

use App\Actions\GetProfileMessage;
use App\Actions\SaveProfileMessage;
use App\Http\Requests\StoreProfileMessageRequest;
use App\Models\ProviderProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileMessageController extends Controller
{
    public function __construct(
        private GetProfileMessage $getProfileMessage,
        private SaveProfileMessage $saveProfileMessage
    ) {
    }

    public function profileMessage(): View
    {
        return view('profile.profile-message', [
            'message' => $this->getProfileMessage->execute(Auth::user()),
        ]);
    }

    public function store(StoreProfileMessageRequest $request): JsonResponse
    {
        $this->authorize('update', ProviderProfile::class);

        $result = $this->saveProfileMessage->execute(
            Auth::user(),
            $request->validated('message')
        );

        return response()->json($result['data'], $result['status']);
    }
}
