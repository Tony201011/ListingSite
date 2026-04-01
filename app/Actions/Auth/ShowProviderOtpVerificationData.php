<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class ShowProviderOtpVerificationData
{
    public function execute(): array
    {
        if (! Session::has('otp_required') || ! Session::has('pending_signup_key')) {
            return [
                'redirect' => '/signup',
                'errors' => [
                    'session' => 'OTP session expired. Please signup again.',
                ],
            ];
        }

        $pendingKey = Session::get('pending_signup_key');
        $pendingUser = Cache::get($pendingKey);

        if (! $pendingUser) {
            return [
                'redirect' => '/signup',
                'errors' => [
                    'session' => 'Signup session expired. Please signup again.',
                ],
            ];
        }

        $otpData = Cache::get($pendingKey.'_otp');
        $remainingTime = 0;

        if ($otpData && isset($otpData['expires_at'])) {
            $remainingTime = max(0, $otpData['expires_at'] - time());
        }

        return [
            'userData' => (object) $pendingUser,
            'remainingTime' => $remainingTime,
        ];
    }
}
