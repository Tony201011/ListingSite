<?php

namespace App\Http\Controllers;

use App\Models\Rate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyRateController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        $rates = $user ? $user->rates()->orderByDesc('created_at')->get() : collect();

        return view('my-rate', [
            'rates' => $rates,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        $validated = $request->validate([
            'description' => 'required|string|max:255',   // now required
            'incall'      => 'required|string|max:50',
            'outcall'     => 'required|string|max:50',
            'extra'       => 'nullable|string',
        ]);

        /** @var \App\Models\User|null $user */

        $rate = $user->rates()->create($validated);

        return response()->json($rate, 201);
    }

    public function update(Request $request, Rate $rate)
    {
        $user = Auth::user();
        if (! $user || $rate->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'description' => 'required|string|max:255',   // now required
            'incall'      => 'required|string|max:50',
            'outcall'     => 'required|string|max:50',
            'extra'       => 'nullable|string',
        ]);

        $rate->update($validated);

        return response()->json($rate);
    }

    public function destroy(Rate $rate)
    {
        $user = Auth::user();
        if (! $user || $rate->user_id !== $user->id) {
            abort(403);
        }

        $rate->delete();

        return response()->json(['success' => true]);
    }
}
