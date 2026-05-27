<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\ValueObjects\AustralianMobile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

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
            'name' => $this->generateAccountName($validated['email']),
            'email' => $validated['email'],
            'mobile' => $normalizedMobile,
            'password' => Hash::make($validated['password']),
            'maskMobile' => $phone->toMasked(),
            'role' => User::ROLE_PROVIDER,
            'mobile_verified' => false,
        ], now()->addMinutes(10));

        Cache::put($pendingKey.'_otp', [
            'code' => $sendResult['otp_hash'],
            'expires_at' => $sendResult['expires_at']->timestamp,
        ], $sendResult['expires_at']->copy()->addMinutes(2));

        Session::put('otp_required', true);
        Session::put('pending_signup_key', $pendingKey);

        return redirect('/otp-verification')
            ->with('success', 'OTP sent successfully. Please verify your mobile number.');
    }

    private function generateAccountName(string $email): string
    {
        $localPart = Str::before($email, '@');
        $cleaned = trim((string) preg_replace('/[^a-zA-Z0-9]+/', ' ', $localPart));

        if ($cleaned === '') {
            return 'Provider';
        }

        return Str::title(Str::limit($cleaned, 255, ''));
    }
}
