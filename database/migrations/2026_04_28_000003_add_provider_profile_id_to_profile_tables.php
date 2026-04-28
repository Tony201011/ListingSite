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
        // Use correlated subquery syntax (compatible with both MySQL and SQLite).

        $tables = array_merge(self::SINGLETON_TABLES, self::MULTI_TABLES, ['availabilities']);

        foreach ($tables as $table) {
            DB::table($table)
                ->whereNull('provider_profile_id')
                ->whereNotNull('user_id')
                ->update([
                    'provider_profile_id' => DB::raw(
                        "(SELECT MIN(id) FROM provider_profiles WHERE provider_profiles.user_id = {$table}.user_id)"
                    ),
                ]);
        }

        // ── 3. Add FK constraints now that data is in place (not supported on SQLite) ──

        if (DB::getDriverName() !== 'sqlite') {
            foreach (array_merge(self::SINGLETON_TABLES, self::MULTI_TABLES, ['availabilities']) as $table) {
                Schema::table($table, function (Blueprint $t): void {
                    $t->foreign('provider_profile_id')
                        ->references('id')->on('provider_profiles')
                        ->cascadeOnDelete();
                });
            }
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
                if (DB::getDriverName() !== 'sqlite') {
                    $t->dropForeign([$table.'_provider_profile_id_foreign']);
                }
                $t->dropColumn('provider_profile_id');
            });
        }
    }
};
