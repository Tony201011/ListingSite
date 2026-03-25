<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ForgetController extends Controller
{
    public function setForget(Request $request){

         return view('set-forget');

    }
}
