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
use App\Services\LocationSlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private BuildProfileFilterViewData $buildProfileFilterViewData,
        private GetProfileShowData $getProfileShowData,
        private FavouriteBookmarkService $favouriteBookmarkService,
        private RecordProfileView $recordProfileView,
        private LocationSlugService $locationSlugService,
    ) {}

    public function index(HomeIndexRequest $request): View|RedirectResponse
    {
        $validated = $request->validated();

        $canonicalUrl = $this->resolveCanonicalSearchUrl($request, $validated);
        if ($canonicalUrl !== null) {
            return redirect()->to($canonicalUrl, 301);
        }

        $viewData = $this->buildProfileFilterViewData->execute($validated, syncWithAdminOnlineListing: true);

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
        ?string $sequence_id = null
    ): View|RedirectResponse {
        $matchedProfiles = $this->getRouteMatchedProfiles($state, $suburb, $slug);

        abort_if($matchedProfiles->isEmpty(), 404);

        $profile = $this->resolveProfileForRequest($matchedProfiles, $sequence_id);
        $canonicalUrl = $this->buildCanonicalProfileUrl($profile);

        if ($this->shouldRedirectToCanonical($request, $canonicalUrl)) {
            $query = $request->getQueryString();

            return redirect()->to($query ? "{$canonicalUrl}?{$query}" : $canonicalUrl, 301);
        }

        $viewData = $this->getProfileShowData->execute(
            $profile->slug,
            (int) $profile->profile_sequence,
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

        return redirect()->to($profile->getEscortUrl(), 301);
    }

    private function getRouteMatchedProfiles(string $state, string $suburb, string $slug): Collection
    {
        $state = strtolower(trim($state));
        $suburb = strtolower(trim($suburb));

        $profiles = ProviderProfile::query()
            ->where('slug', $slug)
            ->where('profile_status', 'approved')
            ->whereHas('user')
            ->whereDoesntHave('hideShowProfile', fn ($query) => $query->where('status', 'hide'))
            ->with(['state', 'city'])
            ->get(['id', 'slug', 'profile_sequence', 'state_id', 'city_id', 'suburb']);

        $locationMatches = $profiles->filter(function (ProviderProfile $profile) use ($state, $suburb): bool {
            return $profile->getStateSlug() === $state && $profile->getSuburbSlug() === $suburb;
        })->values();

        return $locationMatches->isNotEmpty()
            ? $locationMatches
            : $profiles->sortBy('profile_sequence')->values();
    }

    private function resolveProfileForRequest(Collection $profiles, ?string $sequenceId): ProviderProfile
    {
        if ($profiles->count() === 1) {
            return $profiles->first();
        }

        if ($sequenceId !== null) {
            $sequence = (int) $sequenceId;
            $profile = $profiles->firstWhere('profile_sequence', $sequence);

            abort_if($profile === null, 404);

            return $profile;
        }

        return $profiles->sortBy('profile_sequence')->first();
    }

    private function buildCanonicalProfileUrl(ProviderProfile $profile): string
    {
        return $profile->getEscortUrl();
    }

    private function shouldRedirectToCanonical(ShowProfileRequest $request, string $canonicalUrl): bool
    {
        $currentPath = trim($request->getPathInfo(), '/');
        $canonicalPath = trim((string) parse_url($canonicalUrl, PHP_URL_PATH), '/');

        return $currentPath !== $canonicalPath;
    }

    private function resolveCanonicalSearchUrl(HomeIndexRequest $request, array $validated): ?string
    {
        $currentPath = trim($request->getPathInfo(), '/');

        if (! str_starts_with($currentPath, 'escorts/search')) {
            return null;
        }

        $rawQuery = [];
        parse_str((string) $request->server('QUERY_STRING', ''), $rawQuery);

        $location = trim((string) ($validated['location'] ?? ''));
        $locationState = trim((string) ($validated['location_state'] ?? ''));
        $locationData = $this->locationSlugService->fromLocationText($location, $locationState);

        if ($locationData === null || empty($locationData['slug'])) {
            return null;
        }

        $canonicalPath = route('escorts.search.slug', ['location_slug' => $locationData['slug']]);
        $canonicalRoutePath = trim((string) parse_url($canonicalPath, PHP_URL_PATH), '/');

        $query = $rawQuery;
        unset(
            $query['location'],
            $query['location_state'],
            $query['location_slug'],
            $query['location_from_route'],
            $query['user_lat'],
            $query['user_lng']
        );
        $queryString = http_build_query($query);
        $targetUrl = $queryString !== '' ? "{$canonicalPath}?{$queryString}" : $canonicalPath;

        $hasLegacyLocationQuery = array_key_exists('location', $rawQuery)
            || array_key_exists('location_state', $rawQuery)
            || array_key_exists('location_slug', $rawQuery)
            || array_key_exists('location_from_route', $rawQuery);

        if ($currentPath !== $canonicalRoutePath) {
            return $targetUrl;
        }

        return $hasLegacyLocationQuery ? $targetUrl : null;
    }
}
