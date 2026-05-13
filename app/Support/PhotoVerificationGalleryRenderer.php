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
            return collect($urls)
                ->filter(fn ($url) => filled($url))
                ->map(fn ($url): string => (string) $url)
                ->values()
                ->all();
        }

        if (blank($urls)) {
            return [];
        }

        $decoded = json_decode($urls, true);
        if (is_array($decoded)) {
            return collect($decoded)
                ->filter(fn ($url) => filled($url))
                ->map(fn ($url): string => (string) $url)
                ->values()
                ->all();
        }

        return [(string) $urls];
    }
}
