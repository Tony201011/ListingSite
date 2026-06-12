<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ListingPaginationUrlService
{
    private const DEFAULT_MIN_AGE = 18;

    private const DEFAULT_MAX_AGE = 40;

    private const DEFAULT_MIN_PRICE = 150;

    private const DEFAULT_MAX_PRICE = 400;

    public function __construct(
        private readonly LocationSlugService $locationSlugService,
    ) {}

    public function buildContext(array $validated, bool $advancedSearch = false): array
    {
        $locationData = $this->resolveLocationData($validated);
        $usesLocationPath = $locationData !== null && ! empty($locationData['slug']);
        $usesSearchPath = $usesLocationPath || $advancedSearch || $this->hasSearchFilters($validated);

        if ($advancedSearch) {
            // Advanced-search page (/search/*) canonical routes
            if ($usesLocationPath) {
                return [
                    'base_url' => route('search.location', ['location_slug' => $locationData['slug']]),
                    'query' => $this->buildQueryParameters($validated, encodeGirlsInPath: false, encodeLocationInPath: true),
                ];
            }

            if ($usesSearchPath) {
                return [
                    'base_url' => route('advanced-search'),
                    'query' => $this->buildQueryParameters($validated, encodeGirlsInPath: false, encodeLocationInPath: false),
                ];
            }
        } else {
            // Home page (/escorts/search/*) canonical routes
            if ($usesLocationPath) {
                return [
                    'base_url' => route('escorts.search.slug', ['location_slug' => $locationData['slug']]),
                    'query' => $this->buildQueryParameters($validated, encodeGirlsInPath: false, encodeLocationInPath: true),
                ];
            }

            $escortName = trim((string) ($validated['escort_name'] ?? ''));
            if ($escortName !== '') {
                $nameSlug = Str::slug($escortName);
                return [
                    'base_url' => route('escorts.search.name', ['search_name' => $nameSlug]),
                    'query' => $this->buildQueryParameters($validated, encodeGirlsInPath: false, encodeLocationInPath: false, excludeEscortName: true),
                ];
            }

            if ($usesSearchPath) {
                return [
                    'base_url' => route('escorts.search'),
                    'query' => $this->buildQueryParameters($validated, encodeGirlsInPath: false, encodeLocationInPath: false),
                ];
            }
        }

        return [
            'base_url' => route('escorts.index', ['type' => $this->resolveGirlsMode($validated)]),
            'query' => $this->buildQueryParameters($validated, encodeGirlsInPath: true, encodeLocationInPath: false),
        ];
    }

    public function buildUrl(array $validated, int $page = 1, bool $advancedSearch = false): string
    {
        $context = $this->buildContext($validated, $advancedSearch);
        $page = max(1, $page);
        $url = $page === 1 ? $context['base_url'] : rtrim($context['base_url'], '/')."/page/{$page}";

        if ($context['query'] === []) {
            return $url;
        }

        return $url.'?'.http_build_query($context['query']);
    }

    public function canonicalUrlForRequest(Request $request, array $validated, bool $advancedSearch = false): ?string
    {
        // Use the raw QUERY_STRING (before FormRequest::prepareForValidation merges defaults
        // into the GET query bag) so that explicit "girls" or "page" parameters can be detected.
        parse_str((string) $request->server('QUERY_STRING', ''), $rawQuery);

        $routeName = (string) ($request->route()?->getName() ?? '');
        $currentPage = $this->resolveCurrentPage($request);

        if ($advancedSearch) {
            // Only the legacy /advanced-search route needs redirecting to the canonical /search path.
            // All canonical /search/* routes render directly regardless of query parameters.
            if ($routeName !== 'advanced-search.legacy') {
                return null;
            }

            $targetUrl = $this->buildUrl($validated, $currentPage, advancedSearch: true);

            return $this->urlsDiffer($request, $targetUrl) ? $targetUrl : null;
        }

        // Legacy /escorts/search/location/... routes redirect to /escorts/location/{text}.
        if (in_array($routeName, ['escorts.search.location', 'escorts.search.location.no-state', 'escorts.search.location.legacy'], true)) {
            $location = trim((string) ($validated['location'] ?? ''));

            if ($location !== '') {
                return url('/escorts/location/'.rawurlencode($location));
            }

            return null;
        }

        // /escorts/search with a location filter in the query redirects to the advanced-search canonical URL.
        if ($routeName === 'escorts.search') {
            if ($this->resolveLocationData($validated) !== null) {
                $targetUrl = $this->buildUrl($validated, $currentPage, advancedSearch: true);

                return $this->urlsDiffer($request, $targetUrl) ? $targetUrl : null;
            }

            return null;
        }

        // /escorts/location/{text} renders directly. If legacy query params are present they are
        // stripped by redirecting to the canonical /search/{slug} advanced-search URL.
        if ($routeName === 'escorts.location') {
            if (! empty($rawQuery)) {
                $targetUrl = $this->buildUrl($validated, $currentPage, advancedSearch: true);

                return $this->urlsDiffer($request, $targetUrl) ? $targetUrl : null;
            }

            return null;
        }

        // The home page (/) renders directly for any filter combination.
        // Only redirect when explicit girls or page pagination parameters are present.
        if ($routeName === 'home') {
            if (! isset($rawQuery['girls']) && ! isset($rawQuery['page'])) {
                return null;
            }
        }

        // All other canonical SEO home routes render directly without redirect.
        if (in_array($routeName, [
            'escorts.search.page',
            'escorts.search.slug', 'escorts.search.slug.page',
            'escorts.search.name', 'escorts.search.name.page',
        ], true)) {
            return null;
        }

        // For all remaining routes (girls.*, escorts.browse, escorts.index, etc.)
        // apply the existing canonical URL redirect logic.
        $targetUrl = $this->buildUrl($validated, $currentPage, advancedSearch: false);

        // /escorts/all on page 1 with no filters is canonically the home page (/).
        if (
            $currentPage === 1
            && $targetUrl === route('escorts.index', ['type' => 'all'])
        ) {
            $targetUrl = route('home');
        }

        return $this->urlsDiffer($request, $targetUrl) ? $targetUrl : null;
    }

    public function resolveCurrentPage(Request $request): int
    {
        return max(1, (int) ($request->route('page') ?? $request->query('page', 1)));
    }

    private function buildQueryParameters(array $validated, bool $encodeGirlsInPath, bool $encodeLocationInPath, bool $excludeEscortName = false): array
    {
        $query = [];

        if (! $encodeLocationInPath) {
            $location = trim((string) ($validated['location'] ?? ''));
            $locationState = trim((string) ($validated['location_state'] ?? ''));

            if ($location !== '') {
                $query['location'] = $location;
            }

            if ($locationState !== '') {
                $query['location_state'] = $locationState;
            }
        }

        if (! $excludeEscortName) {
            $escortName = trim((string) ($validated['escort_name'] ?? ''));
            if ($escortName !== '') {
                $query['escort_name'] = $escortName;
            }
        }

        $minAge = (int) ($validated['min_age'] ?? self::DEFAULT_MIN_AGE);
        $maxAge = (int) ($validated['max_age'] ?? self::DEFAULT_MAX_AGE);
        $minPrice = (int) ($validated['min_price'] ?? self::DEFAULT_MIN_PRICE);
        $maxPrice = (int) ($validated['max_price'] ?? self::DEFAULT_MAX_PRICE);

        if ($minAge !== self::DEFAULT_MIN_AGE) {
            $query['min_age'] = $minAge;
        }

        if ($maxAge !== self::DEFAULT_MAX_AGE) {
            $query['max_age'] = $maxAge;
        }

        if ($minPrice !== self::DEFAULT_MIN_PRICE) {
            $query['min_price'] = $minPrice;
        }

        if ($maxPrice !== self::DEFAULT_MAX_PRICE) {
            $query['max_price'] = $maxPrice;
        }

        if (isset($validated['distance']) && $validated['distance'] !== '') {
            $query['distance'] = (int) $validated['distance'];
        }

        foreach ((array) ($validated['categories'] ?? []) as $categoryId) {
            if (is_numeric($categoryId)) {
                $query['categories'][] = (int) $categoryId;
            }
        }

        $girlsMode = $this->resolveGirlsMode($validated);
        if (! $encodeGirlsInPath && $girlsMode !== 'all') {
            $query['girls'] = $girlsMode;
        }

        return $query;
    }

    private function hasSearchFilters(array $validated): bool
    {
        return trim((string) ($validated['location'] ?? '')) !== ''
            || trim((string) ($validated['location_state'] ?? '')) !== ''
            || trim((string) ($validated['escort_name'] ?? '')) !== ''
            || ! empty((array) ($validated['categories'] ?? []))
            || (int) ($validated['min_age'] ?? self::DEFAULT_MIN_AGE) !== self::DEFAULT_MIN_AGE
            || (int) ($validated['max_age'] ?? self::DEFAULT_MAX_AGE) !== self::DEFAULT_MAX_AGE
            || (int) ($validated['min_price'] ?? self::DEFAULT_MIN_PRICE) !== self::DEFAULT_MIN_PRICE
            || (int) ($validated['max_price'] ?? self::DEFAULT_MAX_PRICE) !== self::DEFAULT_MAX_PRICE
            || (isset($validated['distance']) && $validated['distance'] !== '');
    }

    private function resolveGirlsMode(array $validated): string
    {
        $girlsMode = trim((string) ($validated['girls'] ?? 'all'));

        return in_array($girlsMode, ['all', 'new', 'popular'], true) ? $girlsMode : 'all';
    }

    private function resolveLocationData(array $validated): ?array
    {
        $location = trim((string) ($validated['location'] ?? ''));
        $locationState = trim((string) ($validated['location_state'] ?? ''));

        if ($location === '') {
            return null;
        }

        return $this->locationSlugService->fromLocationText($location, $locationState);
    }

    private function urlsDiffer(Request $request, string $targetUrl): bool
    {
        $currentPath = trim($request->getPathInfo(), '/');
        $targetPath = trim((string) parse_url($targetUrl, PHP_URL_PATH), '/');

        if ($currentPath !== $targetPath) {
            return true;
        }

        parse_str((string) $request->server('QUERY_STRING', ''), $currentQuery);
        parse_str((string) parse_url($targetUrl, PHP_URL_QUERY), $targetQuery);

        ksort($currentQuery);
        ksort($targetQuery);

        return $currentQuery !== $targetQuery;
    }
}
