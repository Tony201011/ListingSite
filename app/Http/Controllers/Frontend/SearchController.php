<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    private const MAX_SUGGESTIONS = 8;

    public function suggestions(Request $request): JsonResponse
    {
        $term = trim((string) $request->input('q', ''));

        if ($term === '') {
            return response()->json(['suggestions' => []]);
        }

        $results = collect();

        try {
            $results = $this->queryScoutSuggestions($term);
        } catch (\Throwable $e) {
            Log::warning('Scout search unavailable, falling back to database search.', [
                'error' => $e->getMessage(),
            ]);
        }

        $remainingSlots = self::MAX_SUGGESTIONS - $results->count();

        if ($remainingSlots > 0) {
            $results = $results->concat(
                $this->queryDatabaseSuggestions(
                    $term,
                    $results->pluck('id')->all(),
                    $remainingSlots
                )
            );
        }

        $suggestions = $results->map(fn (ProviderProfile $profile) => [
            'name' => $profile->name,
            'slug' => $profile->slug,
            'location' => $this->resolveLocationLabel($profile),
            'age' => $profile->age,
        ])->values();

        return response()->json(['suggestions' => $suggestions]);
    }

    private function resolveLocationLabel(ProviderProfile $profile): string
    {
        $cityName = trim((string) ($profile->city?->name ?? ''));

        if ($cityName !== '') {
            return $cityName;
        }

        $suburb = trim((string) ($profile->suburb ?? ''));

        if ($suburb === '') {
            return '';
        }

        $suburb = preg_replace('/\s+\d{4}\s*$/', '', $suburb) ?? $suburb;

        return trim(strtok($suburb, ',') ?: $suburb);
    }

    private function queryScoutSuggestions(string $term)
    {
        return ProviderProfile::search($term)
            ->where('profile_status', 'approved')
            ->query(fn ($query) => $query
                ->whereNotNull('slug')
                ->where('slug', '!=', '')
                ->whereNull('deleted_at')
                ->where('is_blocked', false)
                ->whereHas('user', fn ($userQuery) => $userQuery->where('role', User::ROLE_PROVIDER))
                ->whereDoesntHave('hideShowProfile', fn ($q) => $q->where('status', 'hide'))
                ->where(fn ($availabilityQuery) => $availabilityQuery
                    ->whereCurrentlyOnline()
                    ->orWhere(fn ($orQuery) => $orQuery->whereCurrentlyAvailableNow())))
            ->take(self::MAX_SUGGESTIONS)
            ->get(['id', 'name', 'slug', 'city_id', 'suburb', 'age'])
            ->load('city');
    }

    private function queryDatabaseSuggestions(string $term, array $excludedProfileIds = [], int $limit = self::MAX_SUGGESTIONS)
    {
        $query = ProviderProfile::query()
            ->where('profile_status', 'approved')
            ->whereNull('deleted_at')
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->where('is_blocked', false)
            ->whereHas('user', fn ($query) => $query->where('role', User::ROLE_PROVIDER))
            ->whereDoesntHave('hideShowProfile', fn ($q) => $q->where('status', 'hide'))
            ->where(fn ($availabilityQuery) => $availabilityQuery
                ->whereCurrentlyOnline()
                ->orWhere(fn ($orQuery) => $orQuery->whereCurrentlyAvailableNow()))
            ->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($term).'%'])
            ->with('city')
            ->orderBy('name')
            ->take($limit)
            ->when(
                ! empty($excludedProfileIds),
                fn ($query) => $query->whereNotIn('id', $excludedProfileIds)
            )
            ->get(['id', 'name', 'slug', 'city_id', 'suburb', 'age']);

        return $query;
    }
}
