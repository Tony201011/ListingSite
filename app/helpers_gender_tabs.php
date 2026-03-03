<?php

use App\Models\Category;
use Illuminate\Support\Facades\Cache;

if (!function_exists('gender_tabs')) {
    function gender_tabs()
    {
        return Cache::rememberForever('gender_tabs_categories', function () {
            $tabs = Category::query()
                ->where('is_active', true)
                ->where('website_type', 'adult')
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->get(['slug', 'name'])
                ->map(fn (Category $category): object => (object) [
                    'slug' => $category->slug,
                    'label' => $category->name,
                ]);

            if ($tabs->isNotEmpty()) {
                return $tabs;
            }

            return collect([
                (object) ['slug' => 'female', 'label' => 'Female'],
                (object) ['slug' => 'male', 'label' => 'Male'],
                (object) ['slug' => 'trans', 'label' => 'Trans'],
                (object) ['slug' => 'couple', 'label' => 'Couple'],
            ]);
        });
    }
}
