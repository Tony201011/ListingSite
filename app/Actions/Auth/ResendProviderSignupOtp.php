<?php

namespace App\Actions\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ResendProviderSignupOtp
{
    public function __construct(
        private SendProviderOtp $sendProviderOtp
    ) {
    }

    public function execute(): array
    {
        if (! Session::has('otp_required') || ! Session::has('pending_signup_key')) {
            return [
                'status' => 422,
                'data' => [
                    'success' => false,
                    'message' => 'OTP session expired. Please signup again.',
                ],
            ];
        }

        $pendingKey = Session::get('pending_signup_key');
        $pendingUser = Cache::get($pendingKey);

        if (! $pendingUser || empty($pendingUser['mobile'])) {
            return [
                'status' => 422,
                'data' => [
                    'success' => false,
                    'message' => 'Signup session expired. Please signup again.',
                ],
            ];
        }

        $resendLockKey = $pendingKey . '_resend_lock';

        if (Cache::has($resendLockKey)) {
            $remainingCooldown = (int) Cache::get($resendLockKey, 0);

            return [
                'status' => 429,
                'data' => [
                    'success' => false,
                    'message' => $remainingCooldown > 0
                        ? "Please wait {$remainingCooldown} seconds before requesting another OTP."
                        : 'Please wait before requesting another OTP.',
                ],
            ];
        }

        $sendResult = $this->sendProviderOtp->execute($pendingUser['mobile']);

        if (! $sendResult['success']) {
            return [
                'status' => 500,
                'data' => [
                    'success' => false,
                    'message' => $sendResult['message'],
                ],
            ];
        }

        $otpExpirySeconds = 120;
        $resendCooldownSeconds = 30;

        Cache::put($pendingKey . '_otp', [
            'code' => Hash::make((string) $sendResult['otp']),
            'expires_at' => $sendResult['expires_at']->timestamp,
        ], $sendResult['expires_at']);

        Cache::put($pendingKey, $pendingUser, now()->addMinutes(10));
        Cache::put($resendLockKey, $resendCooldownSeconds, now()->addSeconds($resendCooldownSeconds));

        Log::info('OTP resent successfully', [
            'mobile' => $pendingUser['mobile'],
        ]);

        return [
            'status' => 200,
            'data' => [
                'success' => true,
                'message' => 'OTP resent successfully.',
                'timer' => $otpExpirySeconds,
                'resend_cooldown' => $resendCooldownSeconds,
            ],
        ];
    }
}
