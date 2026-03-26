<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRateRequest;
use App\Http\Requests\UpdateRateGroupRequest;
use App\Http\Requests\UpdateRateRequest;
use App\Models\Rate;
use App\Models\RateGroup;
use Illuminate\Support\Facades\Auth;

class MyRateController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        $rates = $user
            ? $user->rates()->orderByDesc('created_at')->get()
            : collect();

        $groups = collect([
            (object) ['id' => 1, 'name' => 'Group A'],
            (object) ['id' => 2, 'name' => 'Group B'],
            (object) ['id' => 3, 'name' => 'Group C'],
        ]);

        return view('my-rate', [
            'rates' => $rates,
            'groups' => $groups,
        ]);
    }

    public function store(StoreRateRequest $request)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        $rate = $user->rates()->create($request->validated());

        return response()->json($rate, 201);
    }

    public function update(UpdateRateRequest $request, Rate $rate)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user || $rate->user_id !== $user->id) {
            abort(403);
        }

        $rate->update($request->validated());

        return response()->json($rate);
    }

    public function destroy(Rate $rate)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user || $rate->user_id !== $user->id) {
            abort(403);
        }

        $rate->delete();

        return response()->json(['success' => true]);
    }

    public function updateGroup(UpdateRateGroupRequest $request, RateGroup $group)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user || $group->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $group->update($request->validated());

        return response()->json($group);
    }

    public function destroyGroup(RateGroup $group)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user || $group->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $group->rates()->update(['group_id' => null]);
        $group->delete();

        return response()->json(['success' => true]);
    }
}
