<?php

namespace App\Http\Controllers\Profile;

use App\Actions\CalculateBabeRank;
use App\Actions\GetFrontendSimplePage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class BabeRankController extends Controller
{
    public function __construct(
        private CalculateBabeRank $calculateBabeRank,
        private GetFrontendSimplePage $getFrontendSimplePage
    ) {}

    public function myBabeRank()
    {
        $data = $this->calculateBabeRank->execute(Auth::user());

        return view('profile.my-babe-rank', $data);
    }

    public function babeRank()
    {
        $data = $this->calculateBabeRank->execute(Auth::user());
        $data['page'] = $this->getFrontendSimplePage->babeRankReadMore();

        return view('profile.babe-rank-read-more', $data);
    }
}
