<?php

namespace App\Actions\Auth;

use App\Actions\Support\ActionResult;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class ShowProviderOtpVerificationData
{
    public function execute(): ActionResult
    {
        if (! Session::has('otp_required') || ! Session::has('pending_signup_key')) {
            return ActionResult::domainError(
                'OTP session expired. Please signup again.',
                ['session' => ['OTP session expired. Please signup again.']]
            );
        }

        $pendingKey = Session::get('pending_signup_key');
        $pendingUser = Cache::get($pendingKey);

        if (! $pendingUser) {
            return ActionResult::domainError(
                'Signup session expired. Please signup again.',
                ['session' => ['Signup session expired. Please signup again.']]
            );
        }

        $otpData = Cache::get($pendingKey.'_otp');
        $remainingTime = 0;

        if ($otpData && isset($otpData['expires_at'])) {
            $remainingTime = max(0, $otpData['expires_at'] - time());
        }

        return ActionResult::success([
            'userData' => (object) $pendingUser,
            'remainingTime' => $remainingTime,
        ]);
    }
}
