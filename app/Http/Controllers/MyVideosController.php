<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyVideosController extends Controller
{
    public function index(Request $request)
    {

        // load videos (if you have a model) and pass to view
        return view('my-videos');
    }

    public function uploadVideo(Request $request)
    {

        return view('upload-video');
    }
}
