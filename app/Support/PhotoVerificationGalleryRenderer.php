<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Js;
use Illuminate\Support\Str;

class PhotoVerificationGalleryRenderer
{
    private const JSON_DECODE_DEPTH = 512;

    public static function render(
        array|string|null $urls,
        int $height = 160,
        int $width = 160,
        ?int $previewLimit = null
    ): HtmlString {
        $urls = self::normalizeUrls($urls);

        if (blank($urls)) {
            return new HtmlString('-');
        }

        $safeHeight = max(50, min(500, $height));
        $safeWidth = max(50, min(500, $width));
        $previewUrls = collect($urls)
            ->when(
                filled($previewLimit),
                fn ($collection) => $collection->take(max(1, (int) $previewLimit)),
            )
            ->values();
        $remainingCount = max(count($urls) - $previewUrls->count(), 0);
        $modalId = 'photo-verification-gallery-'.Str::uuid();
        $alpineState = self::buildAlpineState($urls);

        return new HtmlString(
            '<div
                x-data="' . e($alpineState) . '"
                class="space-y-3"
            >' .
            '<div class="flex flex-wrap gap-3">' .
            $previewUrls
                ->values()
                ->map(function (string $url, int $index) use ($safeHeight, $safeWidth): string {
                    return self::buildThumbnailButton($url, $index, $safeHeight, $safeWidth);
                })
                ->implode('') .
            ($remainingCount > 0
                ? '<button
                    type="button"
                    class="flex items-center justify-center rounded-lg border border-dashed border-gray-300 bg-gray-50 text-sm font-medium text-gray-600"
                    style="width: ' . $safeWidth . 'px; height: ' . $safeHeight . 'px;"
                    @click="openGallery(' . $previewUrls->count() . ')"
                >+' . e((string) $remainingCount) . ' more</button>'
                : '') .
            '</div>' .
            '<template x-teleport="body">
                <div
                    x-show="isOpen"
                    x-on:keydown.escape.window="closeGallery()"
                    class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-950/80 p-4"
                    style="display: none;"
                >
                    <div class="absolute inset-0" @click="closeGallery()"></div>

                    <div class="relative z-10 flex max-h-full w-full max-w-6xl flex-col gap-4 rounded-2xl bg-white p-4 shadow-2xl">
                        <div class="flex items-center justify-between gap-4">
                            <div class="text-sm font-medium text-gray-700">Verification photos</div>
                            <button
                                type="button"
                                class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                                @click="closeGallery()"
                            >Close</button>
                        </div>

                        <div class="relative flex min-h-[320px] items-center justify-center overflow-hidden rounded-xl bg-gray-100">
                            <button
                                x-show="images.length > 1"
                                type="button"
                                class="absolute left-3 top-1/2 z-10 -translate-y-1/2 rounded-full bg-white/90 p-2 text-gray-700 shadow"
                                @click="previousImage()"
                            >&larr;</button>

                            <img
                                :src="images[activeIndex]"
                                :alt="`Verification photo ${activeIndex + 1}`"
                                class="max-h-[75vh] w-full rounded-xl object-contain"
                            >

                            <button
                                x-show="images.length > 1"
                                type="button"
                                class="absolute right-3 top-1/2 z-10 -translate-y-1/2 rounded-full bg-white/90 p-2 text-gray-700 shadow"
                                @click="nextImage()"
                            >&rarr;</button>
                        </div>

                        <div class="flex flex-wrap gap-3 overflow-y-auto">
                            ' . collect($urls)
                ->values()
                ->map(function (string $url, int $index) use ($modalId): string {
                    return '<button
                        type="button"
                        class="overflow-hidden rounded-lg border"
                        :class="activeIndex === ' . $index . ' ? \'border-primary-500 ring-2 ring-primary-200\' : \'border-gray-200\'"
                        data-gallery-id="' . e($modalId) . '"
                        @click="openGallery(' . $index . ')"
                    >
                        <img
                            src="' . e($url) . '"
                            alt="' . e('Verification photo '.($index + 1)) . '"
                            class="h-20 w-20 object-cover"
                            loading="lazy"
                            decoding="async"
                        >
                    </button>';
                })
                ->implode('') . '
                        </div>
                    </div>
                </div>
            </template>' .
            '</div>'
        );
    }

    private static function buildThumbnailButton(string $url, int $index, int $height, int $width): string
    {
        return '<button
            type="button"
            class="relative overflow-hidden rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500"
            style="width: ' . $width . 'px; height: ' . $height . 'px;"
            @click="openGallery(' . $index . ')"
        >
            <img
                src="' . e($url) . '"
                alt="' . e('Verification photo '.($index + 1)) . '"
                loading="lazy"
                decoding="async"
                class="h-full w-full object-cover"
            >
        </button>';
    }

    private static function buildAlpineState(array $urls): string
    {
        return "{\n".
            'activeIndex: 0,'."\n".
            'images: '.Js::from(array_values($urls))->toHtml().",\n".
            "isOpen: false,\n".
            "closeGallery() { this.isOpen = false },\n".
            "nextImage() { this.activeIndex = (this.activeIndex + 1) % this.images.length },\n".
            "openGallery(index) { this.activeIndex = index; this.isOpen = true },\n".
            "previousImage() { this.activeIndex = (this.activeIndex - 1 + this.images.length) % this.images.length }\n".
            '}';
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
