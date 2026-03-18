<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use Illuminate\Http\Request;
use App\Models\TourCity;
use Illuminate\Support\Facades\Auth;

class MyToursController extends Controller
{
    /**
     * Display the tours management page with existing tours.
     */
    public function index()
    {
        $tours = Auth::user()->tours()->orderBy('from')->get();
        return view('my-tours', compact('tours'));
    }

    /**
     * Store a newly created tour.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'city'        => 'required|string|max:255',
            'from'        => 'required|date|after_or_equal:now',
            'to'          => 'required|date|after:from',
            'description' => 'nullable|string',
            'enabled'     => 'boolean',
        ]);

        $tour = Auth::user()->tours()->create($validated);

        return response()->json([
            'message' => 'Tour created successfully.',
            'tour'    => $tour,
        ], 201);
    }

    /**
     * Update the specified tour.
     */
    public function update(Request $request, Tour $tour)
    {
        // Ensure the authenticated user owns this tour
        if ($tour->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'city'        => 'required|string|max:255',
            'from'        => 'required|date|after_or_equal:now',
            'to'          => 'required|date|after:from',
            'description' => 'nullable|string',
            'enabled'     => 'boolean',
        ]);

        $tour->update($validated);

        return response()->json([
            'message' => 'Tour updated successfully.',
            'tour'    => $tour,
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

     public function search(Request $request)
    {
        $query = $request->get('q');

        if (!$query || strlen($query) < 2) {
            return response()->json([]);
        }

        $cities = TourCity::where('name', 'like', $query . '%')
            ->orderBy('name')
            ->get(['name', 'state']);

        return response()->json($cities->map(fn($city) => [
            'name'       => $city->name,
            'adminName1' => $city->state,
        ]));
    }
}
