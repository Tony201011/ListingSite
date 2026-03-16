<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MyToursController extends Controller
{
    public function index(Request $request)
    {
        // load any data you need for the view (e.g. existing tours)
        return view('my-tours');
    }
}
