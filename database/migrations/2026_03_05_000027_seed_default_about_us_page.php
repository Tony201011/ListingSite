<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('about_us_pages')) {
            return;
        }

        $hasAnyRecord = DB::table('about_us_pages')->exists();

        if ($hasAnyRecord) {
            return;
        }

        DB::table('about_us_pages')->insert([
            'title' => 'About Us',
            'content' => '<h2>About us Australia\'s newest local escorts directory</h2><p>Premium access, unlimited photos, touring pages, available now, profile boosters and many more features are all inclusive at Real Babes.</p><h3>Why join Realbabes and set up your babe profile</h3><p>Sign up now and be part of something new and exciting.</p><h2>10+ reasons why you should join Realbabes</h2><ul><li>Unlimited photos</li><li>Online photo editor tools</li><li>No weekly or monthly subscriptions</li><li>Easy payments from as low as 10 AUD</li><li>Your credits do not expire</li></ul><p>We focus on profile quality, verification and honest listings so users can connect with confidence.</p>',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('about_us_pages')) {
            return;
        }

        DB::table('about_us_pages')
            ->where('title', 'About Us')
            ->where('content', 'like', '%10+ reasons why you should join Realbabes%')
            ->delete();
    }
};
