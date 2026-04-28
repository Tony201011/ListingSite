<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const SINGLETON_TABLES = [
        'online_users',
        'available_nows',
        'hide_show_profiles',
        'profile_messages',
    ];

    private const MULTI_TABLES = [
        'rates',
        'rate_groups',
        'profile_images',
        'user_videos',
        'tours',
        'photo_verifications',
        'set_and_forgets',
    ];

    private function dropForeignIfExists(string $table, string $fkName): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $exists = DB::selectOne("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND CONSTRAINT_NAME = ?
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$table, $fkName]);

        if ($exists) {
            DB::statement("ALTER TABLE `$table` DROP FOREIGN KEY `$fkName`");
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $exists = DB::selectOne("
            SELECT INDEX_NAME
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND INDEX_NAME = ?
        ", [$table, $indexName]);

        if ($exists) {
            DB::statement("ALTER TABLE `$table` DROP INDEX `$indexName`");
        }
    }

    public function up(): void
    {
        $allTables = array_merge(self::SINGLETON_TABLES, self::MULTI_TABLES, ['availabilities']);

        foreach ($allTables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                if (!Schema::hasColumn($table, 'provider_profile_id')) {
                    $t->unsignedBigInteger('provider_profile_id')->nullable()->after('user_id');
                }
            });
        }

        foreach ($allTables as $table) {
            DB::table($table)
                ->whereNull('provider_profile_id')
                ->whereNotNull('user_id')
                ->update([
                    'provider_profile_id' => DB::raw(
                        "(SELECT MIN(id) FROM provider_profiles WHERE provider_profiles.user_id = {$table}.user_id)"
                    ),
                ]);
        }

        foreach (self::SINGLETON_TABLES as $table) {
            $this->dropForeignIfExists($table, "{$table}_user_id_foreign");
            $this->dropForeignIfExists($table, "{$table}_provider_profile_id_foreign");

            $this->dropIndexIfExists($table, "{$table}_user_id_unique");
            $this->dropIndexIfExists($table, "{$table}_provider_profile_id_unique");

            Schema::table($table, function (Blueprint $t) {
                $t->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $t->foreign('provider_profile_id')->references('id')->on('provider_profiles')->cascadeOnDelete();
                $t->unique('provider_profile_id');
            });
        }

        $this->dropForeignIfExists('availabilities', 'availabilities_user_id_foreign');
        $this->dropForeignIfExists('availabilities', 'availabilities_provider_profile_id_foreign');

        $this->dropIndexIfExists('availabilities', 'availabilities_user_id_day_unique');
        $this->dropIndexIfExists('availabilities', 'availabilities_provider_profile_id_day_unique');

        Schema::table('availabilities', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('provider_profile_id')->references('id')->on('provider_profiles')->cascadeOnDelete();
            $table->unique(['provider_profile_id', 'day']);
        });

        foreach (self::MULTI_TABLES as $table) {
            $this->dropForeignIfExists($table, "{$table}_provider_profile_id_foreign");

            Schema::table($table, function (Blueprint $t) {
                $t->foreign('provider_profile_id')->references('id')->on('provider_profiles')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        $allTables = array_merge(self::SINGLETON_TABLES, self::MULTI_TABLES, ['availabilities']);

        foreach ($allTables as $table) {
            $this->dropForeignIfExists($table, "{$table}_provider_profile_id_foreign");
        }

        foreach (self::SINGLETON_TABLES as $table) {
            $this->dropIndexIfExists($table, "{$table}_provider_profile_id_unique");

            Schema::table($table, function (Blueprint $t) {
                $t->unique('user_id');
            });
        }

        $this->dropIndexIfExists('availabilities', 'availabilities_provider_profile_id_day_unique');

        Schema::table('availabilities', function (Blueprint $table) {
            $table->unique(['user_id', 'day']);
        });

        foreach ($allTables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                if (Schema::hasColumn($table, 'provider_profile_id')) {
                    $t->dropColumn('provider_profile_id');
                }
            });
        }
    }
};
