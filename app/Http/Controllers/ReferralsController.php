<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;

class ReferralsController extends Controller
{
    public function referrals(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }
        $profile = $user->providerProfile;
        $referralLink = $profile?->account_user_referral_code;
        $referralCount = User::where('referral_code', $referralLink)->count();

        return view('referrals', compact('referralLink', 'referralCount'));
    }
}
