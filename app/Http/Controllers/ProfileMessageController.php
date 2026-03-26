<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProfileMessageRequest;
use Illuminate\Support\Facades\Auth;

class ProfileMessageController extends Controller
{
    public function profileMessage()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        $message = $user?->profileMessage;

        return view('profile-message', compact('message'));
    }

    public function storeProfileMessage(StoreProfileMessageRequest $request)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated.',
            ], 401);
        }

        $profileMessage = $user->profileMessage;
        $messageText = $request->validated('message');

        if ($profileMessage) {
            $profileMessage->update([
                'message' => $messageText,
            ]);
        } else {
            $user->profileMessage()->create([
                'message' => $messageText,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile message saved successfully.',
        ]);
    }
}
