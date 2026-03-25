<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ProfileMessageController extends Controller
{
       public function profileMessage(Request $request){

         $message = Auth::user()->profileMessage;
         return view('profile-message', compact('message'));

     }

    public function storeProfileMessage(Request $request)
        {
            $request->validate([
                'message' => 'required|string|max:1000',
            ]);

            $user = Auth::user();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated.',
                ], 401);
            }

            $profileMessage = $user->profileMessage;

            if ($profileMessage) {
                $profileMessage->update([
                    'message' => $request->input('message'),
                ]);
            } else {
                /** @var \App\Models\User $user */
                $user->profileMessage()->create([
                    'message' => $request->input('message'),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile message saved successfully.',
            ]);
        }

}
