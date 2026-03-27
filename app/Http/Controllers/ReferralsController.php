<?php

namespace App\Http\Controllers;

use App\Actions\GetReferralPageData;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReferralsController extends Controller
{
    public function __construct(
        private GetReferralPageData $getReferralPageData
    ) {
    }

    public function referrals(): View
    {
        return view('profile.referrals', $this->getReferralPageData->execute(Auth::user()));
    }
}
