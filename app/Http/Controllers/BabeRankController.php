<?php

namespace App\Http\Controllers;

class BabeRankController extends Controller
{
    public function myBabeRank()
    {
        return view('my-babe-rank');
    }

    public function babeRank()
    {
        return view('babe-rank-read-more');
    }
}
