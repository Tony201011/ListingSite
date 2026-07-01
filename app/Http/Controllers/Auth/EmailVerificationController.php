<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\SendAccountCreatedEmailJob;
use App\Models\EmailLog;
use App\Models\SmtpSetting;
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
            return redirect('/my-profiles');
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

            if ($this->shouldDispatchAccountCreatedEmail($user, $request)) {
                $this->dispatchAccountCreatedEmail($user);
            }
        }

        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/my-profiles')
            ->with('success', 'Your account has been successfully verified.');
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user || $user->hasVerifiedEmail()) {
            return redirect('/my-profiles');
        }

        $result = $this->resendEmailVerification($user);

        if (! $result) {
            return back()->withErrors([
                'email' => 'Unable to resend verification email right now. Please try again.',
            ]);
        }

        return back()->with('success', 'A new verification email has been sent.');
    }

    private function shouldDispatchAccountCreatedEmail(User $user, Request $request): bool
    {
        if ((bool) $request->session()->pull('signup_account_created_pending', false)) {
            return true;
        }

        if ($user->role !== User::ROLE_PROVIDER) {
            return false;
        }

        $createdAt = $user->created_at;

        return $createdAt instanceof \Illuminate\Support\Carbon && $createdAt->between(now()->subHours(24), now());
    }

    private function dispatchAccountCreatedEmail(User $user): void
    {
        $mailSetting = SmtpSetting::query()
            ->where('is_enabled', true)
            ->latest('updated_at')
            ->first()
            ?? SmtpSetting::query()->latest('updated_at')->first();

        if (! $mailSetting) {
            Log::error('Account created email skipped: no mail setting found.', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return;
        }

        SendAccountCreatedEmailJob::dispatchSync($user->id, $mailSetting->id);
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
