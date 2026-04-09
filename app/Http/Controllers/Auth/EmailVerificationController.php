<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailVerificationController extends Controller
{
    public function notice()
    {
        return view('auth.verify-email');
    }

    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();

        $user = $request->user();
        $isAgent = $user && $user->role === User::ROLE_AGENT;

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($isAgent) {
            return redirect('/agent')
                ->with('success', 'Your email has been verified. Please sign in to continue.');
        }

        return redirect('/signin')
            ->with('success', 'Your account has been successfully verified. Please sign in to continue.');
    }

    public function resend(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Verification link sent again.');
    }
}
