<?php

namespace App\Http\Controllers\Frontend;

use App\Actions\BuildProfileFilterViewData;
use App\Actions\GetProfileShowData;
use App\Actions\RecordProfileView;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdvancedSearchRequest;
use App\Http\Requests\HomeIndexRequest;
use App\Http\Requests\ShowProfileRequest;
use App\Services\FavouriteBookmarkService;
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






    public function showProfile(ShowProfileRequest $request, string $slug): View
    {
        $viewData = $this->getProfileShowData->execute(
            $slug,
            $request->validated()
        );
        $viewData['userFavourites'] = $this->favouriteBookmarkService->getFavourites();

        $this->recordProfileView->execute($slug, $request);

        return view('frontend.profile-show', $viewData);
    }
}
