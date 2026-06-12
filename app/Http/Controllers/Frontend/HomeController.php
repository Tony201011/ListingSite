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
use App\Services\ListingPaginationUrlService;
use Illuminate\Http\JsonResponse;
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
        private ListingPaginationUrlService $listingPaginationUrlService,
    ) {}

    public function index(HomeIndexRequest $request): View|RedirectResponse
    {
        $validated = $request->validated();

        $canonicalUrl = $this->listingPaginationUrlService->canonicalUrlForRequest($request, $validated);
        if ($canonicalUrl !== null) {
            return redirect()->to($canonicalUrl, 301);
        }

        // Infinite-scroll pagination requests send X-Listing-Page: 1.
        // Return a minimal partial (grid + pagination only) to skip the heavy
        // filter-category and banner-profile queries that are not needed for
        // subsequent pages.
        if ($request->header('X-Listing-Page')) {
            $pageData = $this->buildProfileFilterViewData->executePageOnly($validated, syncWithAdminOnlineListing: true);

            return view('frontend.partials.listings-page', $pageData);
        }

        $viewData = $this->buildProfileFilterViewData->execute($validated, syncWithAdminOnlineListing: true);

        $viewData['userFavourites'] = $this->favouriteBookmarkService->getFavourites();

        return view('frontend.home', $viewData);
    }

    public function advancedSearch(AdvancedSearchRequest $request): View|RedirectResponse
    {
        $validated = $request->validated();
        $canonicalUrl = $this->listingPaginationUrlService->canonicalUrlForRequest($request, $validated, advancedSearch: true);
        if ($canonicalUrl !== null) {
            return redirect()->to($canonicalUrl, 301);
        }

        $viewData = $this->buildProfileFilterViewData->execute($validated, syncWithAdminOnlineListing: true, advancedSearch: true);
        $viewData['userFavourites'] = $this->favouriteBookmarkService->getFavourites();

        return view('frontend.advanced-search', $viewData);
    }

    public function featuredListings(): View
    {
        $data = $this->buildProfileFilterViewData->getFeaturedListingsData();
        $data['userFavourites'] = $this->favouriteBookmarkService->getFavourites();

        return view('frontend.featured', $data);
    }

    public function sampleListing(): RedirectResponse
    {
        $profile = ProviderProfile::query()
            ->where('profile_status', 'approved')
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->whereHas('user')
            ->whereDoesntHave('hideShowProfile', fn ($query) => $query->where('status', 'hide'))
            ->with(['state', 'city'])
            ->orderBy('id')
            ->first();

        if ($profile === null) {
            return redirect()->route('escorts.search');
        }

        return redirect()->to($profile->getEscortUrl());
    }

    public function favourites(): View
    {
        $favouriteProfileIds = $this->favouriteBookmarkService->getFavourites();
        $profiles = $this->buildProfileFilterViewData->getProfilesBySlugs($favouriteProfileIds);

        return view('frontend.favourites', [
            'profiles' => $profiles,
            'userFavourites' => $favouriteProfileIds,
        ]);
    }

    public function showProfile(
        ShowProfileRequest $request,
        string $state,
        string $suburb,
        string $slug,
        ?string $sequence_id = null
    ): View|RedirectResponse {
        $legacySequenceId = null;
        $matchedProfiles = $this->getRouteMatchedProfiles($state, $suburb, $slug, $legacySequenceId);

        abort_if($matchedProfiles->isEmpty(), 404);

        $profile = $this->resolveProfileForRequest($matchedProfiles, $sequence_id, $legacySequenceId);
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

    private function getRouteMatchedProfiles(string $state, string $suburb, string $slug, ?string &$legacySequenceId = null): Collection
    {
        $state = strtolower(trim($state));
        $suburb = strtolower(trim($suburb));

        $profiles = $this->queryPublicProfilesBySlug($slug);

        if ($profiles->isEmpty()) {
            $legacySlugData = $this->extractLegacySlugWithSequence($slug);

            if ($legacySlugData !== null) {
                $profiles = $this->queryPublicProfilesBySlug($legacySlugData['slug']);
                $legacySequenceId = (string) $legacySlugData['sequence'];
            }
        }

        $locationMatches = $profiles->filter(function (ProviderProfile $profile) use ($state, $suburb): bool {
            return $profile->getStateSlug() === $state && $profile->getSuburbSlug() === $suburb;
        })->values();

        return $locationMatches->isNotEmpty()
            ? $locationMatches
            : $profiles->sortBy('profile_sequence')->values();
    }

    private function resolveProfileForRequest(
        Collection $profiles,
        ?string $sequenceId,
        ?string $legacySequenceId = null
    ): ProviderProfile
    {
        if ($sequenceId !== null) {
            // URL sequence_id "001" maps to profile_sequence 2, "002" → 3, etc.
            $sequence = (int) $sequenceId + 1;
            $profile = $profiles->firstWhere('profile_sequence', $sequence);

            abort_if($profile === null, 404);

            return $profile;
        }

        if ($legacySequenceId !== null) {
            $legacyProfile = $profiles->firstWhere('profile_sequence', (int) $legacySequenceId);

            if ($legacyProfile instanceof ProviderProfile) {
                return $legacyProfile;
            }
        }

        if ($profiles->count() === 1) {
            return $profiles->first();
        }

        return $profiles->sortBy('profile_sequence')->first();
    }

    private function queryPublicProfilesBySlug(string $slug): Collection
    {
        return ProviderProfile::query()
            ->where('slug', $slug)
            ->where('profile_status', 'approved')
            ->whereHas('user')
            ->whereDoesntHave('hideShowProfile', fn ($query) => $query->where('status', 'hide'))
            ->with(['state', 'city'])
            ->get(['id', 'slug', 'profile_sequence', 'state_id', 'city_id', 'suburb']);
    }

    private function extractLegacySlugWithSequence(string $slug): ?array
    {
        if (! preg_match('/^(.+?)(\d{3})$/', $slug, $matches)) {
            return null;
        }

        $legacySlug = trim((string) $matches[1], '-');

        if ($legacySlug === '') {
            return null;
        }

        return [
            'slug' => $legacySlug,
            'sequence' => (int) $matches[2],
        ];
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

    public function listingsOnlineCount(): JsonResponse
    {
        $onlineIds = ProviderProfile::query()
            ->select('id')
            ->whereCurrentlyOnline()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values();

        $homeFeaturedIds = $this->activeBannerPlacementIds('home_featured_expires_at');
        $localBannerIds = $this->activeBannerPlacementIds('local_banner_expires_at');
        $homeBannerIds = $this->activeBannerPlacementIds('home_banner_expires_at');

        return response()->json([
            'online_count' => $onlineIds->count(),
            'home_featured_count' => $homeFeaturedIds->count(),
            'local_banner_count' => $localBannerIds->count(),
            'home_banner_count' => $homeBannerIds->count(),
            'refresh_signature' => hash('sha256', implode('|', [
                $onlineIds->implode(','),
                $homeFeaturedIds->implode(','),
                $localBannerIds->implode(','),
                $homeBannerIds->implode(','),
            ])),
        ]);
    }

    private function activeBannerPlacementIds(string $expiryColumn): Collection
    {
        return ProviderProfile::query()
            ->select('id')
            ->whereNull('provider_profiles.deleted_at')
            ->where('provider_profiles.profile_status', 'approved')
            ->where('provider_profiles.is_blocked', false)
            ->whereHas('user')
            ->whereDoesntHave('hideShowProfile', fn ($query) => $query->where('status', 'hide'))
            ->whereNotNull("provider_profiles.{$expiryColumn}")
            ->where(function ($query) use ($expiryColumn): void {
                $query->where("provider_profiles.{$expiryColumn}", '>', now())
                    ->orWhereDate("provider_profiles.{$expiryColumn}", today());
            })
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->sort()
            ->values();
    }
}
