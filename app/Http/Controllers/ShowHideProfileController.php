<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateShowHideProfileRequest;
use App\Models\HideShowProfile;
use Illuminate\Support\Facades\Auth;

class ShowHideProfileController extends Controller
{
    public function hideShowProfile()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        $status = false;

        if ($user) {
            $available = HideShowProfile::where('user_id', $user->id)->first();
            $status = $available && $available->status === 'show';
        }

        return view('hide-show-profile', compact('status'));
    }

    public function updateHideShowProfile(UpdateShowHideProfileRequest $request)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated.',
            ], 401);
        }

        $status = $request->validated('status');

        $available = HideShowProfile::updateOrCreate(
            ['user_id' => $user->id],
            ['status' => $status]
        );

        return response()->json([
            'success' => true,
            'status' => $available->status,
            'message' => $status === 'show'
                ? 'Your profile is now visible'
                : 'Your profile is now hidden',
        ]);
    }
}
