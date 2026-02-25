<?php

use App\Models\MetaDescription;
use App\Models\MetaKeyword;
use App\Models\MenuItem;
use Illuminate\Support\Facades\Cache;

if (!function_exists('get_meta_description_for_slug')) {
    function get_meta_description_for_slug($slug)
    {
        return MetaDescription::where('page_name', $slug)
            ->where('is_active', true)
            ->value('meta_description');
    }
}

if (!function_exists('get_meta_keywords_for_slug')) {
    function get_meta_keywords_for_slug($slug)
    {
        $keywords = MetaKeyword::where('page_name', $slug)
            ->where('is_active', true)
            ->value('meta_keyword');
        if (is_array($keywords)) {
            return implode(',', $keywords);
        }
        return $keywords;
    }
}

if (!function_exists('main_menu_items')) {
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
