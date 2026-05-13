<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

class PhotoVerificationGalleryRenderer
{
    public static function render(array|string|null $urls, int $height): HtmlString
    {
        $urls = self::normalizeUrls($urls);

        if (blank($urls)) {
            return new HtmlString('-');
        }

        $safeHeight = max(50, min(500, $height));
        return new HtmlString(
            '<div class="flex flex-col gap-3">'.
            collect($urls)
                ->values()
                ->map(function (string $url, int $index) use ($safeHeight): string {
                    $photoNumber = $index + 1;
                    $alt = e("Verification photo {$photoNumber}");
                    $safeUrl = e($url);

                    return '<img src="'.$safeUrl.'" alt="'.$alt.'" loading="lazy" decoding="async" class="w-auto max-w-full rounded-lg border border-gray-200" style="height: '.$safeHeight.'px;">';
                })
                ->implode('')
            .'</div>'
        );
    }

    private static function normalizeUrls(array|string|null $urls): array
    {
        if (is_array($urls)) {
            return self::normalizeUrlArray($urls);
        }

        if (blank($urls)) {
            return [];
        }

        $trimmedUrls = trim($urls);
        $decoded = json_decode($trimmedUrls, true, 8);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return self::normalizeUrlArray($decoded);
        }

        if (str_starts_with($trimmedUrls, '[')) {
            return [];
        }

        return [(string) $urls];
    }

    private static function normalizeUrlArray(array $urls): array
    {
        return collect($urls)
            ->filter(fn ($url) => filled($url))
            ->map(fn ($url): string => (string) $url)
            ->values()
            ->all();
    }
}
