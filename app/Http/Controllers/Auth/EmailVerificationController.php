<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
        $user = $request->user();
        $subject = 'Verify Your Email Address';

        try {
            $user->sendEmailVerificationNotification();

            EmailLog::create([
                'recipient' => $user->email,
                'subject' => $subject,
                'type' => 'verify_email',
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Email verification resend failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            EmailLog::create([
                'recipient' => $user->email,
                'subject' => $subject,
                'type' => 'verify_email',
                'status' => 'failed',
                'error' => $e->getMessage(),
                'sent_at' => now(),
            ]);
        }

        return back()->with('success', 'Verification link sent again.');
    }
}
