<?php

namespace App\Http\Controllers\Profile;

use App\Actions\CalculateBabeRank;
use App\Actions\GetActiveProviderProfile;
use App\Actions\GetFrontendSimplePage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class BabeRankController extends Controller
{
    public function __construct(
        private CalculateBabeRank $calculateBabeRank,
        private GetFrontendSimplePage $getFrontendSimplePage,
        private GetActiveProviderProfile $getActiveProviderProfile
    ) {}

    public function myBabeRank()
    {
        $profile = $this->getActiveProviderProfile->execute(Auth::user());
        $data = $this->calculateBabeRank->execute($profile);

        return view('profile.my-babe-rank', $data);
    }

    public function babeRank()
    {
        $profile = $this->getActiveProviderProfile->execute(Auth::user());
        $data = $this->calculateBabeRank->execute($profile);
        $data['page'] = $this->getFrontendSimplePage->babeRankReadMore();

        return view('profile.babe-rank-read-more', $data);
    }
}
