<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EmailVerificationController extends Controller
{
    public function notice()
    {
        if (request()->user()?->hasVerifiedEmail()) {
            return redirect('/select-profile');
        }

        return view('auth.verify-email');
    }

    public function verify(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = User::find($id);

        if (! $user) {
            abort(404);
        }

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            abort(403, 'Invalid verification link.');
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/select-profile')
            ->with('success', 'Your account has been successfully verified.');
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user || $user->hasVerifiedEmail()) {
            return redirect('/select-profile');
        }

        $result = $this->resendEmailVerification($user);

        if (! $result) {
            return back()->withErrors([
                'email' => 'Unable to resend verification email right now. Please try again.',
            ]);
        }

        return back()->with('success', 'A new verification email has been sent.');
    }

    private function resendEmailVerification(User $user): bool
    {
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

            return true;
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

            return false;
        }
    }
}
