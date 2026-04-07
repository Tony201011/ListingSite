<?php

namespace App\Services;

use App\Models\ProviderProfile;
use Illuminate\Support\Facades\Cache;

class FavouriteBookmarkService
{
    private const TTL_SECONDS = 60 * 60 * 24 * 30; // 30 days

    public function toggleFavourite(string $slug): bool
    {
        return $this->toggle('favourites', $slug);
    }

    public function toggleBookmark(string $slug): bool
    {
        return $this->toggle('bookmarks', $slug);
    }

    public function getFavourites(): array
    {
        return Cache::get($this->cacheKey('favourites'), []);
    }

    public function getBookmarks(): array
    {
        return Cache::get($this->cacheKey('bookmarks'), []);
    }

    public function slugExists(string $slug): bool
    {
        return ProviderProfile::whereNull('deleted_at')
            ->where('profile_status', 'approved')
            ->where('slug', $slug)
            ->exists();
    }

    private function toggle(string $type, string $slug): bool
    {
        $key = $this->cacheKey($type);
        $slugs = Cache::get($key, []);

        if (in_array($slug, $slugs, true)) {
            $slugs = array_values(array_filter($slugs, fn ($s) => $s !== $slug));
            $active = false;
        } else {
            $slugs[] = $slug;
            $active = true;
        }

        Cache::put($key, $slugs, self::TTL_SECONDS);

        return $active;
    }

    private function cacheKey(string $type): string
    {
        if (auth()->check()) {
            return "{$type}_user_" . auth()->id();
        }

        return "{$type}_sess_" . session()->getId();
    }
}
