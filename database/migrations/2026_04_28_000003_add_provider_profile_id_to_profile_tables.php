<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Tables that need provider_profile_id plus a simple (provider_profile_id) unique. */
    private const SINGLETON_TABLES = [
        'online_users',
        'available_nows',
        'hide_show_profiles',
        'profile_messages',
    ];

    /** Tables that only need provider_profile_id added (no unique constraint change). */
    private const MULTI_TABLES = [
        'rates',
        'rate_groups',
        'profile_images',
        'user_videos',
        'tours',
        'photo_verifications',
        'set_and_forgets',
    ];

    public function up(): void
    {
        // ── 1. Add provider_profile_id (nullable, no FK yet) to every table ──

        foreach (array_merge(self::SINGLETON_TABLES, self::MULTI_TABLES, ['availabilities']) as $table) {
            Schema::table($table, function (Blueprint $t) use ($table): void {
                if (! Schema::hasColumn($table, 'provider_profile_id')) {
                    $t->unsignedBigInteger('provider_profile_id')->nullable()->after('user_id');
                }
            });
        }

        // ── 2. Back-fill: set provider_profile_id from the user's first profile ──

        DB::statement('
            UPDATE rates r
            JOIN (
                SELECT user_id, MIN(id) AS profile_id FROM provider_profiles GROUP BY user_id
            ) pp ON r.user_id = pp.user_id
            SET r.provider_profile_id = pp.profile_id
            WHERE r.provider_profile_id IS NULL
        ');

        DB::statement('
            UPDATE rate_groups rg
            JOIN (
                SELECT user_id, MIN(id) AS profile_id FROM provider_profiles GROUP BY user_id
            ) pp ON rg.user_id = pp.user_id
            SET rg.provider_profile_id = pp.profile_id
            WHERE rg.provider_profile_id IS NULL
        ');

        DB::statement('
            UPDATE availabilities a
            JOIN (
                SELECT user_id, MIN(id) AS profile_id FROM provider_profiles GROUP BY user_id
            ) pp ON a.user_id = pp.user_id
            SET a.provider_profile_id = pp.profile_id
            WHERE a.provider_profile_id IS NULL
        ');

        DB::statement('
            UPDATE profile_images pi
            JOIN (
                SELECT user_id, MIN(id) AS profile_id FROM provider_profiles GROUP BY user_id
            ) pp ON pi.user_id = pp.user_id
            SET pi.provider_profile_id = pp.profile_id
            WHERE pi.provider_profile_id IS NULL
        ');

        DB::statement('
            UPDATE user_videos uv
            JOIN (
                SELECT user_id, MIN(id) AS profile_id FROM provider_profiles GROUP BY user_id
            ) pp ON uv.user_id = pp.user_id
            SET uv.provider_profile_id = pp.profile_id
            WHERE uv.provider_profile_id IS NULL
        ');

        DB::statement('
            UPDATE profile_messages pm
            JOIN (
                SELECT user_id, MIN(id) AS profile_id FROM provider_profiles GROUP BY user_id
            ) pp ON pm.user_id = pp.user_id
            SET pm.provider_profile_id = pp.profile_id
            WHERE pm.provider_profile_id IS NULL
        ');

        DB::statement('
            UPDATE tours t
            JOIN (
                SELECT user_id, MIN(id) AS profile_id FROM provider_profiles GROUP BY user_id
            ) pp ON t.user_id = pp.user_id
            SET t.provider_profile_id = pp.profile_id
            WHERE t.provider_profile_id IS NULL
        ');

        DB::statement('
            UPDATE photo_verifications pv
            JOIN (
                SELECT user_id, MIN(id) AS profile_id FROM provider_profiles GROUP BY user_id
            ) pp ON pv.user_id = pp.user_id
            SET pv.provider_profile_id = pp.profile_id
            WHERE pv.provider_profile_id IS NULL
        ');

        DB::statement('
            UPDATE available_nows an
            JOIN (
                SELECT user_id, MIN(id) AS profile_id FROM provider_profiles GROUP BY user_id
            ) pp ON an.user_id = pp.user_id
            SET an.provider_profile_id = pp.profile_id
            WHERE an.provider_profile_id IS NULL
        ');

        DB::statement('
            UPDATE online_users ou
            JOIN (
                SELECT user_id, MIN(id) AS profile_id FROM provider_profiles GROUP BY user_id
            ) pp ON ou.user_id = pp.user_id
            SET ou.provider_profile_id = pp.profile_id
            WHERE ou.provider_profile_id IS NULL
        ');

        DB::statement('
            UPDATE hide_show_profiles hsp
            JOIN (
                SELECT user_id, MIN(id) AS profile_id FROM provider_profiles GROUP BY user_id
            ) pp ON hsp.user_id = pp.user_id
            SET hsp.provider_profile_id = pp.profile_id
            WHERE hsp.provider_profile_id IS NULL
        ');

        DB::statement('
            UPDATE set_and_forgets sf
            JOIN (
                SELECT user_id, MIN(id) AS profile_id FROM provider_profiles GROUP BY user_id
            ) pp ON sf.user_id = pp.user_id
            SET sf.provider_profile_id = pp.profile_id
            WHERE sf.provider_profile_id IS NULL
        ');

        // ── 3. Add FK constraints now that data is in place ──

        foreach (array_merge(self::SINGLETON_TABLES, self::MULTI_TABLES, ['availabilities']) as $table) {
            Schema::table($table, function (Blueprint $t) use ($table): void {
                $t->foreign('provider_profile_id')
                    ->references('id')->on('provider_profiles')
                    ->cascadeOnDelete();
            });
        }

        // ── 4. Singleton tables: swap unique constraint from user_id to provider_profile_id ──

        Schema::table('online_users', function (Blueprint $table): void {
            $table->dropUnique(['user_id']);
            $table->unique('provider_profile_id');
        });

        Schema::table('available_nows', function (Blueprint $table): void {
            $table->dropUnique(['user_id']);
            $table->unique('provider_profile_id');
        });

        Schema::table('hide_show_profiles', function (Blueprint $table): void {
            $table->dropUnique(['user_id']);
            $table->unique('provider_profile_id');
        });

        Schema::table('profile_messages', function (Blueprint $table): void {
            $table->dropUnique(['user_id']);
            $table->unique('provider_profile_id');
        });

        // ── 5. availabilities: swap composite unique from (user_id, day) to (provider_profile_id, day) ──

        Schema::table('availabilities', function (Blueprint $table): void {
            $table->dropUnique(['user_id', 'day']);
            $table->unique(['provider_profile_id', 'day']);
        });
    }

    public function down(): void
    {
        // Restore singleton unique constraints
        Schema::table('online_users', function (Blueprint $table): void {
            $table->dropUnique(['provider_profile_id']);
            $table->unique('user_id');
        });

        Schema::table('available_nows', function (Blueprint $table): void {
            $table->dropUnique(['provider_profile_id']);
            $table->unique('user_id');
        });

        Schema::table('hide_show_profiles', function (Blueprint $table): void {
            $table->dropUnique(['provider_profile_id']);
            $table->unique('user_id');
        });

        Schema::table('profile_messages', function (Blueprint $table): void {
            $table->dropUnique(['provider_profile_id']);
            $table->unique('user_id');
        });

        // Restore availabilities unique constraint
        Schema::table('availabilities', function (Blueprint $table): void {
            $table->dropUnique(['provider_profile_id', 'day']);
            $table->unique(['user_id', 'day']);
        });

        // Drop FK and column from all tables
        foreach (array_merge(self::SINGLETON_TABLES, self::MULTI_TABLES, ['availabilities']) as $table) {
            Schema::table($table, function (Blueprint $t) use ($table): void {
                $t->dropForeign([$table.'_provider_profile_id_foreign']);
                $t->dropColumn('provider_profile_id');
            });
        }
    }
};
