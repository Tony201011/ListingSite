<?php

namespace App\Actions\Auth;

use App\Actions\Support\ActionResult;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class VerifyProviderSignupOtp
{
    private const MAX_ATTEMPTS = 5;

    private const LOCKOUT_MINUTES = 15;

    public function __construct(
        private SendProviderAccountEmails $sendProviderAccountEmails
    ) {}

    public function execute(string $otp): ActionResult
    {
        if (! session()->has('otp_required') || ! session()->has('pending_signup_key')) {
            return ActionResult::domainError('OTP session expired. Please signup again.');
        }

        $pendingKey = session()->get('pending_signup_key');

        // Check if locked out due to too many failed attempts
        $attemptsKey = $pendingKey.'_otp_attempts';
        $attempts = (int) Cache::get($attemptsKey, 0);

        if ($attempts >= self::MAX_ATTEMPTS) {
            $this->expireOtpSession($pendingKey);

            return ActionResult::domainError('Too many failed attempts. Please signup again.', status: 429);
        }

        $pendingUser = Cache::get($pendingKey);
        $otpData = Cache::get($pendingKey.'_otp');

        if (! $pendingUser || ! $otpData || ! isset($otpData['code'], $otpData['expires_at'])) {
            return ActionResult::domainError('OTP expired. Please signup again.');
        }

        if (time() > $otpData['expires_at']) {
            return ActionResult::domainError('OTP expired. Please signup again.');
        }

        // Constant-time comparison: hash the submitted OTP the same way and compare hashes
        if (! isset($otpData['code']) || ! Hash::check((string) $otp, $otpData['code'])) {
            // Increment failed attempts
            Cache::put($attemptsKey, $attempts + 1, now()->addMinutes(self::LOCKOUT_MINUTES));

            $remaining = self::MAX_ATTEMPTS - ($attempts + 1);

            if ($remaining <= 0) {
                $this->expireOtpSession($pendingKey);

                return ActionResult::domainError('Too many failed attempts. Please signup again.', status: 429);
            }

            return ActionResult::validationError('Invalid OTP. '.$remaining.' attempt(s) remaining.');
        }

        if (User::query()->where('email', $pendingUser['email'])->exists()) {
            return ActionResult::domainError('Email already exists.');
        }

        $user = User::create([
            'name' => $pendingUser['name'],
            'email' => $pendingUser['email'],
            'mobile' => $pendingUser['mobile'],
            'password' => $pendingUser['password'],
            'suburb' => $pendingUser['suburb'],
            'role' => $pendingUser['role'],
            'mobile_verified' => true,
            'referral_code' => $pendingUser['referral_code'],
        ]);

        $this->sendProviderAccountEmails->execute($user);

        $this->expireOtpSession($pendingKey);

        Auth::login($user);

        return ActionResult::success([
            'redirect' => route('my-profile'),
        ], 'Account created successfully.');
    }

    private function expireOtpSession(string $pendingKey): void
    {
        Cache::forget($pendingKey);
        Cache::forget($pendingKey.'_otp');
        Cache::forget($pendingKey.'_otp_attempts');

        Session::forget('otp_required');
        Session::forget('pending_signup_key');
    }
}
