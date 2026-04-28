<?php

namespace App\Actions;

use App\Models\ProviderProfile;
use App\Models\User;

class GetReferralPageData
{
    public function execute(?ProviderProfile $profile): array
    {
        abort_if(! $profile, 403);

        $referralLink = $profile->account_user_referral_code;

        $referralCount = $referralLink
            ? User::query()
                ->where('referral_code', $referralLink)
                ->count()
            : 0;

        return [
            'referralLink' => $referralLink,
            'referralCount' => $referralCount,
        ];
    }
}
