<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteAccountRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class AccountController extends Controller
{
    public function deleteAccountPage()
    {
        return view('delete-account');
    }

    public function destroy(DeleteAccountRequest $request)
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login')->with('error', 'Unauthenticated.');
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

            return back()->with(
                'error',
                config('app.debug')
                    ? $e->getMessage()
                    : 'Something went wrong while deleting your account.'
            );
        }
    }
}
