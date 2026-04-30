<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ProviderProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
                ->take(self::MAX_SUGGESTIONS)
                ->get(['id', 'name', 'slug', 'city_id', 'age']);
        } catch (\Throwable) {
            $results = ProviderProfile::query()
                ->where('profile_status', 'approved')
                ->whereNull('deleted_at')
                ->where('name', 'like', '%'.$term.'%')
                ->with('city')
                ->orderBy('name')
                ->take(self::MAX_SUGGESTIONS)
                ->get(['id', 'name', 'slug', 'city_id', 'age']);
        }

        $suggestions = $results->map(fn (ProviderProfile $profile) => [
            'name' => $profile->name,
            'slug' => $profile->slug,
            'location' => $profile->city?->name ?? '',
            'age' => $profile->age,
        ])->values();

        return response()->json(['suggestions' => $suggestions]);
    }
}
