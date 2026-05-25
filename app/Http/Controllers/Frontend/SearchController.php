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

        try {
            $results = ProviderProfile::search($term)
                ->where('profile_status', 'approved')
                ->whereNotNull('slug')
                ->where('slug', '!=', '')
                ->whereNull('deleted_at')
                ->where('is_blocked', false)
                ->whereHas('user', fn ($query) => $query->where('role', User::ROLE_PROVIDER))
                ->where(function ($onlineQuery): void {
                    $onlineQuery
                        ->whereHas('onlineUser', fn ($q) => $q->where('status', 'online'))
                        ->orWhere(fn ($legacy) => $legacy
                            ->whereDoesntHave('onlineUser')
                            ->whereHas('user.onlineUser', fn ($q) => $q
                                ->whereNull('provider_profile_id')
                                ->where('status', 'online')));
                })
                ->whereDoesntHave('hideShowProfile', fn ($q) => $q->where('status', 'hide'))
                ->take(self::MAX_SUGGESTIONS)
                ->get(['id', 'name', 'slug', 'city_id', 'suburb', 'age'])
                ->load('city');
        } catch (\Throwable $e) {
            Log::warning('Scout search unavailable, falling back to database search.', [
                'error' => $e->getMessage(),
            ]);
            $results = ProviderProfile::query()
                ->where('profile_status', 'approved')
                ->whereNull('deleted_at')
                ->whereNotNull('slug')
                ->where('slug', '!=', '')
                ->where('is_blocked', false)
                ->whereHas('user', fn ($query) => $query->where('role', User::ROLE_PROVIDER))
                ->where(function ($onlineConstraint): void {
                    $onlineConstraint
                        ->whereHas('onlineUser', fn ($q) => $q->where('status', 'online'))
                        ->orWhere(fn ($legacy) => $legacy
                            ->whereDoesntHave('onlineUser')
                            ->whereHas('user.onlineUser', fn ($q) => $q
                                ->whereNull('provider_profile_id')
                                ->where('status', 'online')));
                })
                ->whereDoesntHave('hideShowProfile', fn ($q) => $q->where('status', 'hide'))
                ->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($term).'%'])
                ->with('city')
                ->orderBy('name')
                ->take(self::MAX_SUGGESTIONS)
                ->get(['id', 'name', 'slug', 'city_id', 'suburb', 'age']);
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
}
