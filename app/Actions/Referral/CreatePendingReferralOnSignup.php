<?php

namespace App\Actions\Referral;

use App\Models\ProviderProfile;
use App\Models\Referral;
use App\Models\User;

class CreatePendingReferralOnSignup
{
    public function execute(User $user, ?string $referralCode): void
    {
        $referralCode = trim((string) $referralCode);

        if ($referralCode === '') {
            return;
        }

        if (Referral::query()->where('referred_user_id', $user->id)->exists()) {
            return;
        }

        $referrerProfile = ProviderProfile::query()
            ->where('account_user_referral_code', $referralCode)
            ->first();

        if (! $referrerProfile || ! $referrerProfile->user_id) {
            return;
        }

        if ((int) $referrerProfile->user_id === (int) $user->id) {
            return;
        }

        Referral::query()->create([
            'referrer_id' => $referrerProfile->user_id,
            'referred_user_id' => $user->id,
            'referral_code' => $referralCode,
            'status' => 'pending',
        ]);
    }
}
