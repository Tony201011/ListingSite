<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = [
        'rates',
        'rate_groups',
        'profile_images',
        'user_videos',
        'tours',
        'photo_verifications',
        'set_and_forgets',
        'availabilities',
        'online_users',
        'available_nows',
        'hide_show_profiles',
        'profile_messages',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $t): void {
                $t->unsignedBigInteger('user_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $t): void {
                $t->unsignedBigInteger('user_id')->nullable(false)->change();
            });
        }
    }
};
