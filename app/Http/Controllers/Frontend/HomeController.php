<?php

namespace App\Http\Controllers\Frontend;

use App\Actions\BuildProfileFilterViewData;
use App\Actions\GetProfileShowData;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdvancedSearchRequest;
use App\Http\Requests\HomeIndexRequest;
use App\Http\Requests\ShowProfileRequest;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private BuildProfileFilterViewData $buildProfileFilterViewData,
        private GetProfileShowData $getProfileShowData
    ) {}

    public function index(HomeIndexRequest $request): View
    {
        $viewData = $this->buildProfileFilterViewData->execute($request->validated());

        return view('frontend.home', $viewData);
    }

    public function advancedSearch(AdvancedSearchRequest $request): View
    {
        $viewData = $this->buildProfileFilterViewData->execute($request->validated());

        return view('frontend.advanced-search', $viewData);
    }

    public function showProfile(ShowProfileRequest $request, string $slug): View
    {
        $viewData = $this->getProfileShowData->execute(
            $slug,
            $request->validated()
        );

        return view('frontend.profile-show', $viewData);
    }
}
