<?php

namespace App\Http\Controllers;

use App\Models\ProfileImage;
use App\Models\UserVideo;
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

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Unauthenticated.');
        }

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'The password you entered is incorrect.',
            ])->withInput();
        }

        DB::beginTransaction();

        try {
            $disk = Storage::disk('s3');

            // Delete profile images from storage
            $images = ProfileImage::where('user_id', $user->id)->get();

            foreach ($images as $image) {
                if ($image->image_path) {
                    $disk->delete($image->image_path);
                }

                if ($image->thumbnail_path) {
                    $disk->delete($image->thumbnail_path);
                }
            }

            // Delete profile videos from storage
            $videos = UserVideo::where('user_id', $user->id)->get();

            foreach ($videos as $video) {
                if ($video->video_path) {
                    $disk->delete($video->video_path);
                }
            }

            // Delete database rows for uploaded media
            ProfileImage::where('user_id', $user->id)->delete();
            UserVideo::where('user_id', $user->id)->delete();

            /*
            |--------------------------------------------------------------------------
            | Delete other profile/account-related tables here
             |--------------------------------------------------------------------------
            |
             | Example:
             |
             | UserProfile::where('user_id', $user->id)->delete();
             | UserPreference::where('user_id', $user->id)->delete();
             | Subscription::where('user_id', $user->id)->delete();
             |
             */

            // Logout before deleting user
            Auth::logout();

            // Delete user account
            $user->delete();

            DB::commit();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/signin')->with('success', 'Your account has been deleted successfully.');
        } catch (Throwable $e) {
            DB::rollBack();

            return back()->with('error', config('app.debug')
                ? $e->getMessage()
                : 'Something went wrong while deleting your account.');
        }
    }
}
