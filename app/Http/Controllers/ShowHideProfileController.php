<?php

namespace App\Http\Controllers;

use App\Models\HideShowProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ShowHideProfileController extends Controller
{
    public function hideShowProfile(Request $request)
    {
        $user = Auth::user();
        $status = false; // default offline

        if ($user) {
            $available = HideShowProfile::where('user_id', $user->id)->first();
            $status = $available && $available->status === 'show';
        }

        return view('hide-show-profile', compact('status'));
    }

    public function updateHideShowProfile(Request $request)
    {
        $request->validate([
            'status' => 'required|in:show,hide',
        ]);

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated.',
            ], 401);
        }

        $available = HideShowProfile::updateOrCreate(
            ['user_id' => $user->id],
            ['status' => $request->status]
        );

        return response()->json([
            'success' => true,
            'status' => $available->status,
            'message' => $request->status === 'show'
                ? 'Your profile is now visible'
                : 'Your profile is now hidden',
        ]);
    }
}
