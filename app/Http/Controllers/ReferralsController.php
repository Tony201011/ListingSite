<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ReferralsController extends Controller
{
    public function referrals()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        $profile = $user->providerProfile;

        $referralLink = $profile?->account_user_referral_code;

        $referralCount = $referralLink
            ? User::query()
                ->where('referral_code', $referralLink)
                ->count()
            : 0;

        return view('referrals', compact('referralLink', 'referralCount'));
    }
}
