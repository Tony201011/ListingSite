<?php

namespace App\Http\Controllers;

use App\Actions\DeleteTour;
use App\Actions\GetMyToursPageData;
use App\Actions\SearchTourCities;
use App\Actions\StoreTour;
use App\Actions\UpdateTour;
use App\Http\Requests\SearchTourCityRequest;
use App\Http\Requests\StoreTourRequest;
use App\Http\Requests\UpdateTourRequest;
use App\Models\Tour;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MyToursController extends Controller
{
    public function __construct(
        private GetMyToursPageData $getMyToursPageData,
        private StoreTour $storeTour,
        private UpdateTour $updateTour,
        private DeleteTour $deleteTour,
        private SearchTourCities $searchTourCities
    ) {
    }

    /**
     * Display the tours management page with existing tours.
     */
    public function index(): View
    {
        return view('profile.my-tours', $this->getMyToursPageData->execute(Auth::user()));
    }

    /**
     * Store a newly created tour.
     */
    public function store(StoreTourRequest $request): JsonResponse
    {
        $tour = $this->storeTour->execute(
            Auth::user(),
            $request->validated()
        );

        return response()->json([
            'message' => 'Tour created successfully.',
            'tour' => $tour,
        ], 201);
    }

    /**
     * Update the specified tour.
     */
    public function update(UpdateTourRequest $request, Tour $tour): JsonResponse
    {
        $tour = $this->updateTour->execute(
            Auth::user(),
            $tour,
            $request->validated()
        );

        return response()->json([
            'message' => 'Tour updated successfully.',
            'tour' => $tour,
        ]);
    }

    /**
     * Remove the specified tour (soft delete).
     */
    public function destroy(Tour $tour): JsonResponse
    {
        $this->deleteTour->execute(Auth::user(), $tour);

        return response()->json([
            'message' => 'Tour deleted successfully.',
        ]);
    }

    public function search(SearchTourCityRequest $request): JsonResponse
    {
        $cities = $this->searchTourCities->execute(
            $request->validated('q')
        );

        return response()->json($cities);
    }
}
