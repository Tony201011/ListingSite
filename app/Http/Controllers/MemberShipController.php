<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberShipController extends Controller
{
    //
    public function membership(): View
    {
        return view('membership');
    }
}
