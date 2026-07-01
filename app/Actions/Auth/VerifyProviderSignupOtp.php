<?php

namespace App\Actions\Auth;

use App\Actions\Support\ActionResult;
use App\Actions\Referral\CreatePendingReferralOnSignup;
use App\Models\ComplianceConfirmation;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class VerifyProviderSignupOtp
{
    private const MAX_ATTEMPTS = 5;

    private const LOCKOUT_MINUTES = 15;

    public function __construct(
        private SendProviderAccountEmails $sendProviderAccountEmails,
        private CreatePendingReferralOnSignup $createPendingReferralOnSignup,
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

        if (User::query()->where('name', $pendingUser['name'])->exists()) {
            return ActionResult::domainError('Account name already exists.');
        }

        $user = User::create([
            'name' => $pendingUser['name'],
            'email' => $pendingUser['email'],
            'mobile' => $pendingUser['mobile'],
            'password' => $pendingUser['password'],
            'role' => $pendingUser['role'],
            'mobile_verified' => true,
            'referral_code' => $pendingUser['referral_code'],
        ]);

        $acceptedAt = now();
        if (filled($pendingUser['confirmation_accepted_at'] ?? null)) {
            try {
                $acceptedAt = Carbon::parse((string) $pendingUser['confirmation_accepted_at']);
            } catch (\Throwable) {
                $acceptedAt = now();
            }
        }

        ComplianceConfirmation::query()->create([
            'user_id' => $user->id,
            'confirmation_type' => ComplianceConfirmation::TYPE_AGE_CONTENT_OWNERSHIP,
            'context' => ComplianceConfirmation::CONTEXT_SIGNUP,
            'accepted' => (bool) ($pendingUser['age_confirm'] ?? false),
            'accepted_at' => $acceptedAt,
            'ip_address' => $pendingUser['confirmation_ip'] ?? null,
        ]);

        ComplianceConfirmation::query()->create([
            'user_id' => $user->id,
            'confirmation_type' => ComplianceConfirmation::TYPE_CONTENT_POLICY,
            'context' => ComplianceConfirmation::CONTEXT_SIGNUP,
            'accepted' => (bool) ($pendingUser['content_policy_confirm'] ?? false),
            'accepted_at' => $acceptedAt,
            'ip_address' => $pendingUser['confirmation_ip'] ?? null,
        ]);

        $this->createPendingReferralOnSignup->execute($user, $pendingUser['referral_code'] ?? null);

        $this->sendProviderAccountEmails->execute($user);

        request()->session()->put('signup_account_created_pending', true);

        Auth::login($user);
        request()->session()->regenerate();

        $this->expireOtpSession($pendingKey);

        return ActionResult::success([
            'redirect' => route('verification.notice'),
        ], 'Your account has been successfully created! 🎉Before you proceed further, please verify your email address..');
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
