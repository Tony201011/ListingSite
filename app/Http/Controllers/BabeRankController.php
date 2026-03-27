<?php

namespace App\Http\Controllers;

class BabeRankController extends Controller
{
    public function myBabeRank()
    {
        return view('profile.my-babe-rank');
    }

    public function babeRank()
    {
        return view('profile.babe-rank-read-more');
    }
}
