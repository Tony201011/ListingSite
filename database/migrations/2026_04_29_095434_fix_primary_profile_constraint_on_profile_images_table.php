<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Replace the per-user primary-image constraint with a per-profile constraint.
     *
     * The previous migration enforced "one primary image per user", which breaks
     * when a user owns multiple ProviderProfiles because the second profile cannot
     * upload a primary image (unique constraint violation). This migration drops
     * that constraint and introduces an equivalent one scoped to provider_profile_id
     * so each profile can independently have exactly one primary image.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS "uq_one_primary_per_user"');
            DB::statement('
                CREATE UNIQUE INDEX IF NOT EXISTS "uq_one_primary_per_profile"
                ON "profile_images" ("provider_profile_id")
                WHERE "is_primary" = 1 AND "deleted_at" IS NULL
            ');

            return;
        }

        DB::statement('ALTER TABLE `profile_images` DROP INDEX `uq_one_primary_per_user`');
        DB::statement('ALTER TABLE `profile_images` DROP COLUMN `primary_user_constraint`');

        DB::statement('
            ALTER TABLE `profile_images`
            ADD COLUMN `primary_profile_constraint` BIGINT UNSIGNED
            AS (CASE WHEN `is_primary` = 1 AND `deleted_at` IS NULL THEN `provider_profile_id` ELSE NULL END) VIRTUAL
        ');

        DB::statement('
            CREATE UNIQUE INDEX `uq_one_primary_per_profile`
            ON `profile_images` (`primary_profile_constraint`)
        ');
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS "uq_one_primary_per_profile"');
            DB::statement('
                CREATE UNIQUE INDEX IF NOT EXISTS "uq_one_primary_per_user"
                ON "profile_images" ("user_id")
                WHERE "is_primary" = 1 AND "deleted_at" IS NULL
            ');

            return;
        }

        DB::statement('ALTER TABLE `profile_images` DROP INDEX `uq_one_primary_per_profile`');
        DB::statement('ALTER TABLE `profile_images` DROP COLUMN `primary_profile_constraint`');

        DB::statement('
            ALTER TABLE `profile_images`
            ADD COLUMN `primary_user_constraint` BIGINT UNSIGNED
            AS (CASE WHEN `is_primary` = 1 AND `deleted_at` IS NULL THEN `user_id` ELSE NULL END) VIRTUAL
        ');

        DB::statement('
            CREATE UNIQUE INDEX `uq_one_primary_per_user`
            ON `profile_images` (`primary_user_constraint`)
        ');
    }
};
