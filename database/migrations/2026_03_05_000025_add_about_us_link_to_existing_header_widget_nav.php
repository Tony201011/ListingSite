<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('header_widgets')
            ->orderBy('id')
            ->get(['id', 'main_nav_links'])
            ->each(function (object $widget): void {
                $links = json_decode((string) ($widget->main_nav_links ?? '[]'), true);

                if (! is_array($links)) {
                    $links = [];
                }

                $hasAboutUs = collect($links)->contains(function ($item): bool {
                    if (! is_array($item)) {
                        return false;
                    }

                    $label = strtolower(trim((string) ($item['label'] ?? '')));
                    $url = trim((string) ($item['url'] ?? ''));

                    return $label === 'about us' || str_contains($url, '/about-us');
                });

                if ($hasAboutUs) {
                    return;
                }

                array_splice($links, 1, 0, [[
                    'label' => 'About us',
                    'url' => url('/about-us'),
                ]]);

                DB::table('header_widgets')
                    ->where('id', $widget->id)
                    ->update([
                        'main_nav_links' => json_encode($links, JSON_UNESCAPED_UNICODE),
                        'updated_at' => now(),
                    ]);
            });
    }

    public function down(): void
    {
        DB::table('header_widgets')
            ->orderBy('id')
            ->get(['id', 'main_nav_links'])
            ->each(function (object $widget): void {
                $links = json_decode((string) ($widget->main_nav_links ?? '[]'), true);

                if (! is_array($links)) {
                    return;
                }

                $filtered = collect($links)
                    ->filter(function ($item): bool {
                        if (! is_array($item)) {
                            return true;
                        }

                        $label = strtolower(trim((string) ($item['label'] ?? '')));
                        $url = trim((string) ($item['url'] ?? ''));

                        return ! ($label === 'about us' || str_contains($url, '/about-us'));
                    })
                    ->values()
                    ->all();

                DB::table('header_widgets')
                    ->where('id', $widget->id)
                    ->update([
                        'main_nav_links' => json_encode($filtered, JSON_UNESCAPED_UNICODE),
                        'updated_at' => now(),
                    ]);
            });
    }
};
