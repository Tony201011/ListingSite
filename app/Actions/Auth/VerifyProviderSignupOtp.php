<?php

namespace App\Actions\Auth;

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
    ) {
    }

    public function execute(string $otp): array
    {
        if (! session()->has('otp_required') || ! session()->has('pending_signup_key')) {
            return [
                'status' => 422,
                'data' => [
                    'success' => false,
                    'message' => 'OTP session expired. Please signup again.',
                ],
            ];
        }

        $pendingKey = session()->get('pending_signup_key');

        // Check if locked out due to too many failed attempts
        $attemptsKey = $pendingKey . '_otp_attempts';
        $attempts = (int) Cache::get($attemptsKey, 0);

        if ($attempts >= self::MAX_ATTEMPTS) {
            $this->expireOtpSession($pendingKey);

            return [
                'status' => 429,
                'data' => [
                    'success' => false,
                    'message' => 'Too many failed attempts. Please signup again.',
                ],
            ];
        }

        $pendingUser = Cache::get($pendingKey);
        $otpData = Cache::get($pendingKey . '_otp');

        if (! $pendingUser || ! $otpData || ! isset($otpData['code'], $otpData['expires_at'])) {
            return [
                'status' => 422,
                'data' => [
                    'success' => false,
                    'message' => 'OTP expired. Please signup again.',
                ],
            ];
        }

        if (time() > $otpData['expires_at']) {
            return [
                'status' => 422,
                'data' => [
                    'success' => false,
                    'message' => 'OTP expired. Please signup again.',
                ],
            ];
        }

        // Constant-time comparison: hash the submitted OTP the same way and compare hashes
        if (! isset($otpData['code']) || ! Hash::check((string) $otp, $otpData['code'])) {
            // Increment failed attempts
            Cache::put($attemptsKey, $attempts + 1, now()->addMinutes(self::LOCKOUT_MINUTES));

            $remaining = self::MAX_ATTEMPTS - ($attempts + 1);

            if ($remaining <= 0) {
                $this->expireOtpSession($pendingKey);

                return [
                    'status' => 429,
                    'data' => [
                        'success' => false,
                        'message' => 'Too many failed attempts. Please signup again.',
                    ],
                ];
            }

            return [
                'status' => 422,
                'data' => [
                    'success' => false,
                    'message' => 'Invalid OTP. ' . $remaining . ' attempt(s) remaining.',
                ],
            ];
        }

        if (User::query()->where('email', $pendingUser['email'])->exists()) {
            return [
                'status' => 422,
                'data' => [
                    'success' => false,
                    'message' => 'Email already exists.',
                ],
            ];
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

        return [
            'status' => 200,
            'data' => [
                'success' => true,
                'message' => 'Account created successfully.',
                'redirect' => route('my-profile'),
            ],
        ];
    }

    private function expireOtpSession(string $pendingKey): void
    {
        Cache::forget($pendingKey);
        Cache::forget($pendingKey . '_otp');
        Cache::forget($pendingKey . '_otp_attempts');

        Session::forget('otp_required');
        Session::forget('pending_signup_key');
    }
}
