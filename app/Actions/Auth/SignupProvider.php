<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\ValueObjects\AustralianMobile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class SignupProvider
{
    public function __construct(
        private SendProviderOtp $sendProviderOtp
    ) {}

    public function execute(array $validated): RedirectResponse
    {
        $phone = AustralianMobile::fromString($validated['mobile']);
        $normalizedMobile = $phone->toLocal();

        $pendingKey = 'provider_signup_'.md5($validated['email'].'|'.$normalizedMobile);

        $sendResult = $this->sendProviderOtp->execute($normalizedMobile);

        if (! $sendResult['success']) {
            return back()->withErrors([
                'mobile' => $sendResult['message'],
            ])->withInput();
        }

        Cache::put($pendingKey, [
            'name' => $validated['nickname'],
            'email' => $validated['email'],
            'mobile' => $normalizedMobile,
            'password' => Hash::make($validated['password']),
            'suburb' => $validated['suburb'],
            'maskMobile' => $phone->toMasked(),
            'role' => User::ROLE_PROVIDER,
            'mobile_verified' => false,
            'referral_code' => $validated['referral_code'] ?? null,
            'account_user_referral_code' => $validated['account_user_referral_code'] ?? null,
        ], now()->addMinutes(10));

        Cache::put($pendingKey.'_otp', [
            'code' => $sendResult['otp_hash'],
            'expires_at' => $sendResult['expires_at']->timestamp,
        ], $sendResult['expires_at']);

        Session::put('otp_required', true);
        Session::put('pending_signup_key', $pendingKey);

        return redirect('/otp-verification')
            ->with('success', 'OTP sent successfully. Please verify your mobile number.');
    }
}
