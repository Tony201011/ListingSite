<?php

namespace App\Http\Controllers\Profile;

use App\Actions\CalculateBabeRank;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class BabeRankController extends Controller
{
    public function __construct(private CalculateBabeRank $calculateBabeRank) {}

    public function myBabeRank()
    {
        $data = $this->calculateBabeRank->execute(Auth::user());

        return view('profile.my-babe-rank', $data);
    }

    public function babeRank()
    {
        $data = $this->calculateBabeRank->execute(Auth::user());

        return view('profile.babe-rank-read-more', $data);
    }
}
