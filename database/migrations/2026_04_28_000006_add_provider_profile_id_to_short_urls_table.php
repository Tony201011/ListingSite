<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function dropForeignIfExists(string $table, string $fkName): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $exists = DB::selectOne('
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND CONSTRAINT_NAME = ?
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ', [$table, $fkName]);

        if ($exists) {
            DB::statement("ALTER TABLE `$table` DROP FOREIGN KEY `$fkName`");
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $exists = DB::selectOne('
            SELECT INDEX_NAME
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND INDEX_NAME = ?
        ', [$table, $indexName]);

        if ($exists) {
            DB::statement("ALTER TABLE `$table` DROP INDEX `$indexName`");
        }
    }

    public function up(): void
    {
        Schema::table('short_urls', function (Blueprint $table) {
            if (! Schema::hasColumn('short_urls', 'provider_profile_id')) {
                $table->unsignedBigInteger('provider_profile_id')->nullable()->after('user_id');
            }
        });

        // Backfill provider_profile_id from the user's first profile
        DB::table('short_urls')
            ->whereNull('provider_profile_id')
            ->whereNotNull('user_id')
            ->update([
                'provider_profile_id' => DB::raw(
                    '(SELECT MIN(id) FROM provider_profiles WHERE provider_profiles.user_id = short_urls.user_id)'
                ),
            ]);

        $this->dropForeignIfExists('short_urls', 'short_urls_user_id_foreign');
        $this->dropIndexIfExists('short_urls', 'short_urls_user_id_unique');

        Schema::table('short_urls', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->foreign('provider_profile_id')->references('id')->on('provider_profiles')->cascadeOnDelete();
            $table->unique('provider_profile_id');
        });
    }

    public function down(): void
    {
        $this->dropForeignIfExists('short_urls', 'short_urls_provider_profile_id_foreign');
        $this->dropIndexIfExists('short_urls', 'short_urls_provider_profile_id_unique');

        Schema::table('short_urls', function (Blueprint $table) {
            if (Schema::hasColumn('short_urls', 'provider_profile_id')) {
                $table->dropColumn('provider_profile_id');
            }

            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique('user_id');
        });
    }
};
