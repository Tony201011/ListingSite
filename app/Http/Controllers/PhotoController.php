<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PhotoController extends Controller
{
    public function index(Request $request)
    {

       // load any data you need for the view (e.g. existing photos)
        return view('add-photo');
    }

    public function getPhotos(Request $request)
    {

        return view('photos');
    }
}
