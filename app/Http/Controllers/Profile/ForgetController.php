<?php
namespace App\Http\Controllers\Profile;
use App\Http\Controllers\Controller;

class ForgetController extends Controller
{
    public function setForget()
    {
        return view('profile.set-forget');
    }
}
