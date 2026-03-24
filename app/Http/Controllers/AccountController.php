<?php

namespace App\Http\Controllers;

use App\Models\ProfileImage;
use App\Models\UserVideo;
use App\Models\ProviderProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AccountController extends Controller
{
    public function deleteAccountPage()
    {
        return view('delete-account');
    }

    public function destroy(Request $request)
        {
            $request->validate([
                'password' => ['required'],
                'confirmation_text' => ['required', 'in:DELETE'],
            ], [
                'confirmation_text.in' => 'You must type DELETE exactly to confirm account deletion.',
            ]);

            $user = $request->user();

            if (! $user) {
                return redirect()->route('login')->with('error', 'Unauthenticated.');
            }

            if (! Hash::check($request->password, $user->password)) {
                return back()->withErrors([
                    'password' => 'The password you entered is incorrect.',
                ])->withInput();
            }

            try {
                DB::transaction(function () use ($user, $request) {
                    /*
                    |--------------------------------------------------------------------------
                    | Mark account for delayed permanent purge
                    |--------------------------------------------------------------------------
                    */
                    $user->account_status = 'soft_deleted';
                    $user->scheduled_purge_at = now()->addDays(30); // change to 60/90 if needed
                    $user->save();

                    /*
                    |--------------------------------------------------------------------------
                    | Optional: revoke API tokens now
                    |--------------------------------------------------------------------------
                    */
                    if (method_exists($user, 'tokens')) {
                        $user->tokens()->delete();
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Optional: clear remember token
                    |--------------------------------------------------------------------------
                    */
                    $user->setRememberToken(null);
                    $user->save();

                    ProfileImage::where('user_id', $user->id)->delete();
                    UserVideo::where('user_id', $user->id)->delete();
                    ProviderProfile::where('user_id', $user->id)->delete();

                    /*
                    |--------------------------------------------------------------------------
                    | Soft delete user now
                    |--------------------------------------------------------------------------
                    */
                    $user->delete();

                    /*
                    |--------------------------------------------------------------------------
                    | Logout + invalidate session
                    |--------------------------------------------------------------------------
                    */
                    Auth::logout();

                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                });

                return redirect('/signin')->with(
                    'success',
                    'Your account has been deleted and scheduled for permanent removal.'
                );
            } catch (Throwable $e) {
                report($e);

                return back()->with('error', config('app.debug')
                    ? $e->getMessage()
                    : 'Something went wrong while deleting your account.');
            }
        }
}
