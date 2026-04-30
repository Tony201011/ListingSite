<?php

namespace App\Http\Controllers\Profile;

use App\Actions\DeleteTour;
use App\Actions\GetActiveProviderProfile;
use App\Actions\GetMyToursPageData;
use App\Actions\SearchTourCities;
use App\Actions\StoreTour;
use App\Actions\UpdateTour;
use App\Http\Controllers\Controller;
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
        private SearchTourCities $searchTourCities,
        private GetActiveProviderProfile $getActiveProviderProfile
    ) {}

    /**
     * Display the tours management page with existing tours.
     */
    public function index(): View
    {
        $this->authorize('viewAny', Tour::class);

        $profile = $this->getActiveProviderProfile->execute(Auth::user());

        return view('profile.my-tours', $this->getMyToursPageData->execute($profile));
    }

    /**
     * Store a newly created tour.
     */
    public function store(StoreTourRequest $request): JsonResponse
    {
        $this->authorize('create', Tour::class);

        $profile = $this->getActiveProviderProfile->execute(Auth::user());

        $result = $this->storeTour->execute(
            $profile,
            $request->validated()
        );

        return response()->json($result->toPayload(), $result->status());
    }

    /**
     * Update the specified tour.
     */
    public function update(UpdateTourRequest $request, Tour $tour): JsonResponse
    {
        $this->authorize('update', $tour);

        $profile = $this->getActiveProviderProfile->execute(Auth::user());

        $result = $this->updateTour->execute(
            $profile,
            $tour,
            $request->validated()
        );

        return response()->json($result->toPayload(), $result->status());
    }

    /**
     * Remove the specified tour (soft delete).
     */
    public function destroy(Tour $tour): JsonResponse
    {
        $this->authorize('delete', $tour);

        $profile = $this->getActiveProviderProfile->execute(Auth::user());

        $result = $this->deleteTour->execute($profile, $tour);

        return response()->json($result->toPayload(), $result->status());
    }

    /**
     * Toggle the enabled status of the specified tour.
     */
    public function toggleEnabled(Tour $tour): JsonResponse
    {
        $this->authorize('update', $tour);

        $tour->update(['enabled' => ! $tour->enabled]);

        return response()->json(['tour' => $tour]);
    }

    public function search(SearchTourCityRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Tour::class);

        $cities = $this->searchTourCities->execute(
            $request->validated('q')
        );

        return response()->json($cities);
    }
}
