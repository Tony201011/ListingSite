<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetYourAvailabilityController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // load any availability data as needed
        return view('set-your-availability', [
            'user' => $user,
        ]);
    }
}
