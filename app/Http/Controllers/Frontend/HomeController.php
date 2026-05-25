<?php

namespace App\Http\Controllers\Frontend;

use App\Actions\BuildProfileFilterViewData;
use App\Actions\GetProfileShowData;
use App\Actions\RecordProfileView;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdvancedSearchRequest;
use App\Http\Requests\HomeIndexRequest;
use App\Http\Requests\ShowProfileRequest;
use App\Models\ProviderProfile;
use App\Services\FavouriteBookmarkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private BuildProfileFilterViewData $buildProfileFilterViewData,
        private GetProfileShowData $getProfileShowData,
        private FavouriteBookmarkService $favouriteBookmarkService,
        private RecordProfileView $recordProfileView,
    ) {}

    public function index(HomeIndexRequest $request): View
    {
        $viewData = $this->buildProfileFilterViewData->execute($request->validated());
        $viewData['userFavourites'] = $this->favouriteBookmarkService->getFavourites();
        $viewData['userBookmarks'] = $this->favouriteBookmarkService->getBookmarks();

        return view('frontend.home', $viewData);
    }

    public function advancedSearch(AdvancedSearchRequest $request): View
    {
        $viewData = $this->buildProfileFilterViewData->execute($request->validated());
        $viewData['userFavourites'] = $this->favouriteBookmarkService->getFavourites();
        $viewData['userBookmarks'] = $this->favouriteBookmarkService->getBookmarks();

        return view('frontend.advanced-search', $viewData);
    }

    public function featuredListings(): View
    {
        $data = $this->buildProfileFilterViewData->getFeaturedListingsData();
        $data['userFavourites'] = $this->favouriteBookmarkService->getFavourites();

        return view('frontend.featured', $data);
    }

    public function favourites(): View
    {
        $favouriteSlugs = $this->favouriteBookmarkService->getFavourites();
        $profiles = $this->buildProfileFilterViewData->getProfilesBySlugs($favouriteSlugs);

        return view('frontend.favourites', [
            'profiles' => $profiles,
            'userFavourites' => $favouriteSlugs,
        ]);
    }

    public function showProfile(
        ShowProfileRequest $request,
        string $state,
        string $suburb,
        string $slug,
        string $sequence_id
    ): View {
        $viewData = $this->getProfileShowData->execute(
            $slug,
            (int) $sequence_id,
            $request->validated()
        );

        if ($viewData['offline'] ?? false) {
            return view('frontend.profile-offline', $viewData);
        }

        $viewData['userFavourites'] = $this->favouriteBookmarkService->getFavourites();

        $this->recordProfileView->execute($slug, $request);

        return view('frontend.profile-show', $viewData);
    }

    /**
     * Redirect legacy /profile/{slug} URLs to the new SEO-friendly URL.
     * Uses the profile with the lowest sequence number for the given slug.
     */
    public function redirectOldProfile(string $slug): RedirectResponse
    {
        $profile = ProviderProfile::query()
            ->where('slug', $slug)
            ->orderBy('profile_sequence')
            ->with(['state', 'city'])
            ->first();

        if ($profile === null) {
            abort(404);
        }

        return redirect()->route('profile.show', [
            'state'       => $profile->getStateSlug(),
            'suburb'      => $profile->getSuburbSlug(),
            'slug'        => $profile->slug,
            'sequence_id' => $profile->getSequenceFormatted(),
        ], 301);
    }
}
