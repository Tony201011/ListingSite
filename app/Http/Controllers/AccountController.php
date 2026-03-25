<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
            DB::transaction(function () use ($user) {
                $user->account_status = 'soft_deleted';
                $user->scheduled_purge_at = now()->addDays(30); // change to 60/90 if needed
                $user->setRememberToken(null);
                $user->save();

                if (method_exists($user, 'tokens')) {
                    $user->tokens()->delete();
                }

                $user->delete(); // soft delete only
            });

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

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
