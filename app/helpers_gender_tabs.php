<?php

use App\Models\GenderTab;
use Illuminate\Support\Facades\Cache;

if (!function_exists('gender_tabs')) {
    function gender_tabs()
    {
        return Cache::rememberForever('gender_tabs', function () {
            return GenderTab::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        });
    }
}
