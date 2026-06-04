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

        // Start with DB matches so currently available profiles are always included,
        // even when Scout indexes are stale or incomplete.
        $results = $this->queryDatabaseSuggestions($term, [], self::MAX_SUGGESTIONS);

        $remainingSlots = self::MAX_SUGGESTIONS - $results->count();

        if ($remainingSlots > 0) {
            try {
                $results = $results->concat(
                    $this->queryScoutSuggestions(
                        $term,
                        $results->pluck('id')->all(),
                        $remainingSlots
                    )
                );
            } catch (\Throwable $e) {
                Log::warning('Scout search unavailable while supplementing search suggestions.', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $suggestions = $results->map(fn (ProviderProfile $profile) => [
            'name' => $profile->name,
            'slug' => $profile->slug,
            'location' => $this->resolveLocationLabel($profile),
            'age' => $profile->age,
            'url' => $profile->getEscortUrl(),
            'image' => $profile->primaryProfileImage?->thumbnail_url ?? $profile->primaryProfileImage?->image_url,
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

    private function queryScoutSuggestions(string $term, array $excludedProfileIds = [], int $limit = self::MAX_SUGGESTIONS)
    {
        return ProviderProfile::search($term)
            ->where('profile_status', 'approved')
            ->query(fn ($query) => $query
                ->whereNotNull('slug')
                ->where('slug', '!=', '')
                ->whereNull('deleted_at')
                ->where('is_blocked', false)
                ->when(
                    ! empty($excludedProfileIds),
                    fn ($query) => $query->whereNotIn('id', $excludedProfileIds)
                )
                ->whereHas('user', fn ($userQuery) => $userQuery
                    ->where('role', User::ROLE_PROVIDER)
                    ->where('account_status', 'active')
                    ->where('is_blocked', false)
                    ->whereNull('deleted_at'))
                ->whereDoesntHave('hideShowProfile', fn ($q) => $q->where('status', 'hide'))
                ->where(fn ($availabilityQuery) => $this->applyActiveAvailabilityConstraint($availabilityQuery)))
            ->take($limit)
            ->get(['id', 'name', 'slug', 'city_id', 'suburb', 'age', 'state_id'])
            ->load(['city', 'state', 'primaryProfileImage']);
    }

    private function queryDatabaseSuggestions(string $term, array $excludedProfileIds = [], int $limit = self::MAX_SUGGESTIONS)
    {
        return ProviderProfile::query()
            ->where('profile_status', 'approved')
            ->whereNull('deleted_at')
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->where('is_blocked', false)
            ->whereHas('user', fn ($query) => $query
                ->where('role', User::ROLE_PROVIDER)
                ->where('account_status', 'active')
                ->where('is_blocked', false)
                ->whereNull('deleted_at'))
            ->whereDoesntHave('hideShowProfile', fn ($q) => $q->where('status', 'hide'))
            ->where(function ($query): void {
                $this->applyActiveAvailabilityConstraint($query);
            })
            ->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($term).'%'])
            ->with(['city', 'state', 'primaryProfileImage'])
            ->orderBy('name')
            ->take($limit)
            ->when(
                ! empty($excludedProfileIds),
                fn ($query) => $query->whereNotIn('id', $excludedProfileIds)
            )
            ->get(['id', 'name', 'slug', 'city_id', 'suburb', 'age', 'state_id']);
    }

    private function applyActiveAvailabilityConstraint($query): void
    {
        $query->where(function ($availabilityQuery): void {
            $availabilityQuery
                ->where(function ($onlineQuery): void {
                    $onlineQuery->whereHas('onlineUsers', fn ($profileOnlineQuery) => $profileOnlineQuery
                        ->whereNotNull('provider_profile_id')
                        ->where('status', 'online')
                        ->where(function ($onlineExpiryQuery): void {
                            $onlineExpiryQuery->whereNull('online_expires_at')
                                ->orWhere('online_expires_at', '>', now());
                        }));
                })
                ->orWhere(function ($legacyQuery): void {
                    $legacyQuery->whereDoesntHave(
                        'onlineUsers',
                        fn ($profileOnlineQuery) => $profileOnlineQuery->whereNotNull('provider_profile_id')
                    )->whereHas('user', fn ($userQuery) => $userQuery->whereHas(
                        'legacyOnlineUsers',
                        fn ($legacyOnlineQuery) => $legacyOnlineQuery
                            ->where('status', 'online')
                            ->where(function ($onlineExpiryQuery): void {
                                $onlineExpiryQuery->whereNull('online_expires_at')
                                    ->orWhere('online_expires_at', '>', now());
                            })
                    ));
                })
                ->orWhere(fn ($orQuery) => $orQuery->whereCurrentlyAvailableNow());
        });
    }
}
