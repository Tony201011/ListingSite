<?php

namespace App\Actions;

use App\Models\Category;
use Illuminate\Support\Facades\Log;

class GetProfileCategoryOptions
{
    private const WEBSITE_TYPE = 'adult';

    public function execute(string $slug): array
    {
        $parentId = Category::query()
            ->where('slug', $slug)
            ->where('website_type', self::WEBSITE_TYPE)
            ->where('is_active', true)
            ->value('id');

        if (! $parentId) {
            Log::warning('Profile category parent slug not found or inactive.', [
                'slug' => $slug,
                'route' => request()->path(),
            ]);

            return [];
        }

        $options = Category::query()
            ->where('parent_id', $parentId)
            ->where('website_type', self::WEBSITE_TYPE)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('name', 'id')
            ->all();

        if (empty($options)) {
            Log::warning('Profile category options are empty for active parent.', [
                'slug' => $slug,
                'parent_id' => $parentId,
                'route' => request()->path(),
            ]);
        }

        return $options;
    }
}
