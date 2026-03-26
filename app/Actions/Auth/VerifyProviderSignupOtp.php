<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class VerifyProviderSignupOtp
{
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

        if ((string) $otp !== (string) $otpData['code']) {
            return [
                'status' => 422,
                'data' => [
                    'success' => false,
                    'message' => 'Invalid OTP.',
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

        Cache::forget($pendingKey);
        Cache::forget($pendingKey . '_otp');

        Session::forget('otp_required');
        Session::forget('pending_signup_key');

        Auth::login($user);

        return [
            'status' => 200,
            'data' => [
                'success' => true,
                'message' => 'Account created successfully.',
                'redirect' => url('/signin'),
            ],
        ];
    }
}
