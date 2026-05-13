<?php

namespace App\Support;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Log;

class PhotoVerificationGalleryRenderer
{
    private const JSON_DECODE_DEPTH = 512;

    public static function render(array|string|null $urls, int $height = 160, int $width = 160): HtmlString
    {
        $urls = self::normalizeUrls($urls);

        if (blank($urls)) {
            return new HtmlString('-');
        }

        $safeHeight = max(50, min(500, $height));
        $safeWidth = max(50, min(500, $width));

        return new HtmlString(
            '<div class="flex flex-wrap gap-3">' .
            collect($urls)
                ->values()
                ->map(function (string $url, int $index) use ($safeHeight, $safeWidth): string {
                    $photoNumber = $index + 1;
                    $alt = e("Verification photo {$photoNumber}");
                    $safeUrl = e($url);

                    return '<img
                        src="' . $safeUrl . '"
                        alt="' . $alt . '"
                        loading="lazy"
                        decoding="async"
                        class="rounded-lg border border-gray-200 object-cover"
                        style="width: ' . $safeWidth . 'px; height: ' . $safeHeight . 'px;"
                    >';
                })
                ->implode('') .
            '</div>'
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

        $trimmedState = trim($urls);

        $decoded = json_decode($trimmedState, true, self::JSON_DECODE_DEPTH);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return self::normalizeUrlArray($decoded);
        }

        if (str_starts_with($trimmedState, '[')) {
            Log::warning('Malformed photo verification URLs JSON payload encountered while rendering gallery.');

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
