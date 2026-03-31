<?php

namespace App\Actions\Auth;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
class SignupProvider
{
    public function __construct(
        private SendProviderOtp $sendProviderOtp
    ) {
    }

    public function execute(array $validated): RedirectResponse
    {
        $mobile = $validated['mobile'];
        $pendingKey = 'provider_signup_' . md5($validated['email'] . '|' . $validated['mobile']);

        $sendResult = $this->sendProviderOtp->execute($mobile);
        $maskMobile = $this->maskMobile($mobile);


        if (! $sendResult['success']) {
            return back()->withErrors([
                'mobile' => $sendResult['message'],
            ])->withInput();
        }

        Cache::put($pendingKey, [
            'name' => $validated['nickname'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'],
            'password' => Hash::make($validated['password']),
            'suburb' => $validated['suburb'],
            'maskMobile' => $maskMobile,
            'role' => User::ROLE_PROVIDER,
            'mobile_verified' => false,
            'referral_code' => $validated['referral_code'] ?? null,
            'account_user_referral_code' => $validated['account_user_referral_code'] ?? null,
        ], now()->addMinutes(10));

        Cache::put($pendingKey . '_otp', [
            'code' => $sendResult['otp_hash'],
            'expires_at' => $sendResult['expires_at']->timestamp,
        ], $sendResult['expires_at']);

        Session::put('otp_required', true);
        Session::put('pending_signup_key', $pendingKey);

        return redirect('/otp-verification')
            ->with('success', 'OTP sent successfully. Please verify your mobile number.');
    }

    private function maskMobile(string $mobile): string
    {
        $length = strlen($mobile);

        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return str_repeat('*', $length - 4) . substr($mobile, -4);
    }


}
