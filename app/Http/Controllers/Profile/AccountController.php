<?php

namespace App\Http\Controllers\Profile;
use App\Http\Controllers\Controller;
use App\Actions\DeleteUserAccount;
use App\Http\Requests\DeleteAccountRequest;
use Illuminate\Support\Facades\Auth;
use Throwable;

class AccountController extends Controller
{
    public function __construct(
        private DeleteUserAccount $deleteUserAccount
    ) {
    }

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
            $this->deleteUserAccount->execute($user);

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
