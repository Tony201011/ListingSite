<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class AgeVerificationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->session()->put('age_verified', true);

        $cookie = Cookie::make('age_verified', '1', 60 * 24 * 365, '/', null, null, false);

        return response()->json(['status' => 'ok'])->withCookie($cookie);
    }
}
