<?php

namespace App\Http\Controllers;

use App\Models\Rate;
use App\Models\RateGroup; // adjust if your group model has a different name
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyRateController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        $rates = $user ? $user->rates()->orderByDesc('created_at')->get() : collect();

         $groups = collect([
        (object) ['id' => 1, 'name' => 'Group A'],
        (object) ['id' => 2, 'name' => 'Group B'],
        (object) ['id' => 3, 'name' => 'Group C'],
    ]);

    return view('my-rate', [
        'rates'  => $rates,
        'groups' => $groups,
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

     /**
     * Update the specified rate group.
     */
    public function updateGroup(Request $request, RateGroup $group)
    {
        $user = Auth::user();
        if (! $user || $group->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $group->update($validated);

        return response()->json($group);
    }

    /**
     * Remove the specified rate group.
     * This will set group_id to null for all rates in this group.
     */
    public function destroyGroup(RateGroup $group)
    {
        $user = Auth::user();
        if (! $user || $group->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Detach rates from this group (set group_id to null)
        $group->rates()->update(['group_id' => null]);

        // Delete the group
        $group->delete();

        return response()->json(['success' => true]);
    }
}
