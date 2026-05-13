<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

class PhotoVerificationGalleryRenderer
{
    public static function render(?array $urls, int $height): HtmlString
    {
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
}
