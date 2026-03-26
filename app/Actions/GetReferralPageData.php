<?php

namespace App\Actions;

use App\Models\User;

class GetReferralPageData
{
    public function execute(?User $user): array
    {
        abort_if(! $user, 403);

        $profile = $user->providerProfile;
        $referralLink = $profile?->account_user_referral_code;

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
