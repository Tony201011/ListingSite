<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgeVerificationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->session()->put('age_verified', true);

        return response()->json(['status' => 'ok']);
    }
}
