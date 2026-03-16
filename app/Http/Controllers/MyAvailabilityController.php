<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyAvailabilityController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // adjust as needed (e.g. load availability records)
        $availability = $user?->availability ?? null;

        return view('my-availability', [
            'user' => $user,
            'availability' => $availability,
        ]);
    }
}
