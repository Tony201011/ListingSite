<?php

use App\Models\MenuItem;
use App\Models\MetaDescription;
use App\Models\MetaKeyword;
use Illuminate\Support\Facades\Cache;

if (! function_exists('resolve_meta_page_candidates')) {
    function resolve_meta_page_candidates(?string $slug = null): array
    {
        $candidates = [];

        $normalizedSlug = trim((string) $slug, '/');
        if ($normalizedSlug !== '') {
            $candidates[] = $normalizedSlug;
        }

        $path = trim((string) request()->path(), '/');
        if ($path === '') {
            $path = 'home';
        }
        $candidates[] = $path;

        if (str_contains($path, '/')) {
            $segments = explode('/', $path);
            $candidates[] = $segments[0];
        }

        $routeName = request()->route()?->getName();
        if (is_string($routeName) && $routeName !== '') {
            $candidates[] = $routeName;
            $candidates[] = str_replace('.', '-', $routeName);
        }

        $aliases = [
            'terms-and-conditions' => 'terms-conditions',
            'anti-spam-policy' => 'anti-spam',
            'about-us' => 'about',
            'contact-us' => 'contact',
            'help' => 'help-center',
            'advanced-search' => 'search',
            'profile-show' => 'profile',
        ];

        foreach ($candidates as $candidate) {
            if (isset($aliases[$candidate])) {
                $candidates[] = $aliases[$candidate];
            }
        }

        return array_values(array_unique(array_filter($candidates)));
    }
}

if (! function_exists('get_meta_description_for_slug')) {
    function get_meta_description_for_slug($slug = null)
    {
        foreach (resolve_meta_page_candidates($slug) as $candidate) {
            $description = MetaDescription::where('page_name', $candidate)
                ->where('is_active', true)
                ->value('meta_description');

            if (! empty($description)) {
                return $description;
            }
        }

        return null;
    }
}

if (! function_exists('get_meta_keywords_for_slug')) {
    function get_meta_keywords_for_slug($slug = null)
    {
        foreach (resolve_meta_page_candidates($slug) as $candidate) {
            $keywords = MetaKeyword::where('page_name', $candidate)
                ->where('is_active', true)
                ->value('meta_keyword');

            if (is_array($keywords)) {
                return implode(',', $keywords);
            }

            if (! empty($keywords)) {
                return $keywords;
            }
        }

        return null;
    }
}

if (! function_exists('main_menu_items')) {
    function main_menu_items()
    {
        return Cache::rememberForever('main_menu_items', function () {
            return MenuItem::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        });
    }
}
