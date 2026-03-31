<?php

namespace App\Http\Controllers\Profile;
use App\Http\Controllers\Controller;

use App\Actions\GetReferralPageData;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReferralsController extends Controller
{
    public function __construct(
        private GetReferralPageData $getReferralPageData
    ) {
    }

    public function referral(): View
    {
        return view('profile.referral', $this->getReferralPageData->execute(Auth::user()));
    }
}
