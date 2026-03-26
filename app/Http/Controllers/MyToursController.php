<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchTourCityRequest;
use App\Http\Requests\StoreTourRequest;
use App\Http\Requests\UpdateTourRequest;
use App\Models\Tour;
use App\Models\TourCity;
use Illuminate\Support\Facades\Auth;

class MyToursController extends Controller
{
    /**
     * Display the tours management page with existing tours.
     */
    public function index()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        $tours = $user
            ? $user->tours()->orderBy('from')->get()
            : collect();

        return view('my-tours', compact('tours'));
    }

    /**
     * Store a newly created tour.
     */
    public function store(StoreTourRequest $request)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user) {
            abort(403, 'Unauthorized action.');
        }

        $tour = $user->tours()->create($request->validated());

        return response()->json([
            'message' => 'Tour created successfully.',
            'tour' => $tour,
        ], 201);
    }

    /**
     * Update the specified tour.
     */
    public function update(UpdateTourRequest $request, Tour $tour)
    {
        if ($tour->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $tour->update($request->validated());

        return response()->json([
            'message' => 'Tour updated successfully.',
            'tour' => $tour,
        ]);
    }

    /**
     * Remove the specified tour (soft delete).
     */
    public function destroy(Tour $tour)
    {
        if ($tour->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $tour->delete();

        return response()->json([
            'message' => 'Tour deleted successfully.',
        ]);
    }

    public function search(SearchTourCityRequest $request)
    {
        $query = $request->validated('q');

        if (! $query || strlen($query) < 2) {
            return response()->json([]);
        }

        $cities = TourCity::where('name', 'like', $query . '%')
            ->orderBy('name')
            ->get(['name', 'state']);

        return response()->json(
            $cities->map(fn ($city) => [
                'name' => $city->name,
                'adminName1' => $city->state,
            ])
        );
    }
}
