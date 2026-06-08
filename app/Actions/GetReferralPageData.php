<?php

namespace App\Actions;

use App\Models\ProviderProfile;
use App\Models\Referral;
use Illuminate\Support\Str;

class GetReferralPageData
{
    public function execute(?ProviderProfile $profile): array
    {
        abort_if(! $profile, 403);

        $referralCode = $this->ensureReferralCode($profile);
        $referralLink = $referralCode ? url('/signup?ref='.$referralCode) : null;

        $referralCount = $profile->user_id
            ? Referral::query()
                ->where('referrer_id', $profile->user_id)
                ->whereIn('status', ['pending', 'qualified', 'rewarded'])
                ->count()
            : 0;

        return [
            'referralCode' => $referralCode,
            'referralLink' => $referralLink,
            'referralCount' => $referralCount,
        ];
    }

    private function ensureReferralCode(ProviderProfile $profile): ?string
    {
        $existingCode = trim((string) $profile->account_user_referral_code);

        if ($existingCode !== '') {
            return $existingCode;
        }

        if (! $profile->user_id) {
            return null;
        }

        $seed = (string) $profile->user_id.'|'.(string) optional($profile->user)->email;

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $candidate = Str::lower(Str::substr(hash('sha256', $seed.'|'.$attempt), 0, 10));

            $isTakenByAnotherUser = ProviderProfile::query()
                ->where('account_user_referral_code', $candidate)
                ->where('user_id', '!=', $profile->user_id)
                ->exists();

            if ($isTakenByAnotherUser) {
                continue;
            }

            $profile->forceFill([
                'account_user_referral_code' => $candidate,
            ])->save();

            return $candidate;
        }

        return null;
    }
}
