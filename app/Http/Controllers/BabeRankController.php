<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BabeRankController extends Controller
{
     public function myBabeRank(Request $request){

         return view('my-babe-rank');

     }

    public function babeRank(Request $request){

         return view('babe-rank-read-more');

     }

}
