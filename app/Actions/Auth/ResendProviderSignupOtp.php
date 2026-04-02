<?php

namespace App\Actions\Auth;

use App\Actions\Support\ActionResult;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ResendProviderSignupOtp
{
    private const MAX_RESENDS = 5;

    private const RESEND_WINDOW_MINUTES = 15;

    public function __construct(
        private SendProviderOtp $sendProviderOtp
    ) {}

    public function execute(): ActionResult
    {
        if (! Session::has('otp_required') || ! Session::has('pending_signup_key')) {
            return ActionResult::domainError('OTP session expired. Please signup again.');
        }

        $pendingKey = Session::get('pending_signup_key');
        $pendingUser = Cache::get($pendingKey);

        if (! $pendingUser || empty($pendingUser['mobile'])) {
            return ActionResult::domainError('Signup session expired. Please signup again.');
        }

        // Limit total resend count per signup session
        $resendCountKey = $pendingKey.'_resend_count';
        $resendCount = (int) Cache::get($resendCountKey, 0);

        if ($resendCount >= self::MAX_RESENDS) {
            return ActionResult::domainError('Too many OTP resend requests. Please signup again.', status: 429);
        }

        $resendLockKey = $pendingKey.'_resend_lock';

        if (Cache::has($resendLockKey)) {
            $remainingCooldown = (int) Cache::get($resendLockKey, 0);

            return ActionResult::domainError(
                $remainingCooldown > 0
                    ? "Please wait {$remainingCooldown} seconds before requesting another OTP."
                    : 'Please wait before requesting another OTP.',
                status: 429
            );
        }

        $sendResult = $this->sendProviderOtp->execute($pendingUser['mobile']);

        if (! $sendResult['success']) {
            return ActionResult::infrastructureFailure($sendResult['message']);
        }

        $otpExpirySeconds = 300;
        $resendCooldownSeconds = 30;

        Cache::put($pendingKey.'_otp', [
            'code' => $sendResult['otp_hash'],
            'expires_at' => $sendResult['expires_at']->timestamp,
        ], $sendResult['expires_at']->copy()->addMinutes(2));

        // Reset failed verification attempts on resend
        Cache::forget($pendingKey.'_otp_attempts');

        Cache::put($pendingKey, $pendingUser, now()->addMinutes(10));
        Cache::put($resendLockKey, $resendCooldownSeconds, now()->addSeconds($resendCooldownSeconds));
        Cache::put($resendCountKey, $resendCount + 1, now()->addMinutes(self::RESEND_WINDOW_MINUTES));

        Log::info('OTP resent successfully', [
            'mobile' => $pendingUser['maskMobile'] ?? '***',
        ]);

        return ActionResult::success(
            [
                'timer' => $otpExpirySeconds,
                'resend_cooldown' => $resendCooldownSeconds,
            ],
            'OTP resent successfully.'
        );
    }
}
