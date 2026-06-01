<?php

namespace App\Actions;

use App\Models\ProviderProfile;
use App\Models\Referral;

class GetReferralPageData
{
    public function execute(?ProviderProfile $profile): array
    {
        abort_if(! $profile, 403);

        $referralCode = $profile->account_user_referral_code;
        $referralLink = $referralCode ? url('/signup?ref='.$referralCode) : null;

        $referralCount = $profile->user_id
            ? Referral::query()
                ->where('referrer_id', $profile->user_id)
                ->whereIn('status', ['pending', 'qualified', 'rewarded'])
                ->count()
            : 0;

        return [
            'referralLink' => $referralLink,
            'referralCount' => $referralCount,
        ];
    }
}
