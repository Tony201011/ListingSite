<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FavouriteBookmarkController extends Controller
{
    private const TTL_SECONDS = 60 * 60 * 24 * 30; // 30 days

    public function toggleFavourite(Request $request, string $slug): JsonResponse
    {
        $key = $this->cacheKey('favourites');
        $slugs = Cache::get($key, []);

        if (in_array($slug, $slugs, true)) {
            $slugs = array_values(array_filter($slugs, fn ($s) => $s !== $slug));
            $active = false;
        } else {
            $slugs[] = $slug;
            $active = true;
        }

        Cache::put($key, $slugs, self::TTL_SECONDS);

        return response()->json(['active' => $active]);
    }

    public function toggleBookmark(Request $request, string $slug): JsonResponse
    {
        $key = $this->cacheKey('bookmarks');
        $slugs = Cache::get($key, []);

        if (in_array($slug, $slugs, true)) {
            $slugs = array_values(array_filter($slugs, fn ($s) => $s !== $slug));
            $active = false;
        } else {
            $slugs[] = $slug;
            $active = true;
        }

        Cache::put($key, $slugs, self::TTL_SECONDS);

        return response()->json(['active' => $active]);
    }

    public static function getUserFavourites(): array
    {
        return Cache::get(static::buildCacheKey('favourites'), []);
    }

    public static function getUserBookmarks(): array
    {
        return Cache::get(static::buildCacheKey('bookmarks'), []);
    }

    private function cacheKey(string $type): string
    {
        return static::buildCacheKey($type);
    }

    private static function buildCacheKey(string $type): string
    {
        if (auth()->check()) {
            return "{$type}_user_" . auth()->id();
        }

        return "{$type}_sess_" . session()->getId();
    }
}
