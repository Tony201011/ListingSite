<?php

namespace App\Http\Controllers\Profile;

use App\Actions\GetActiveProviderProfile;
use App\Actions\GetReferralPageData;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReferralsController extends Controller
{
    public function __construct(
        private GetReferralPageData $getReferralPageData,
        private GetActiveProviderProfile $getActiveProviderProfile
    ) {}

    public function referral(): View
    {
        $profile = $this->getActiveProviderProfile->execute(Auth::user());

        return view('profile.referral', $this->getReferralPageData->execute($profile));
    }
}
