<?php

namespace App\Http\Controllers;

use App\Actions\GetProfileMessage;
use App\Actions\SaveProfileMessage;
use App\Http\Requests\StoreProfileMessageRequest;
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
        return view('profile-message', [
            'message' => $this->getProfileMessage->execute(Auth::user()),
        ]);
    }

    public function storeProfileMessage(StoreProfileMessageRequest $request): JsonResponse
    {
        $result = $this->saveProfileMessage->execute(
            Auth::user(),
            $request->validated('message')
        );

        return response()->json($result['data'], $result['status']);
    }
}
