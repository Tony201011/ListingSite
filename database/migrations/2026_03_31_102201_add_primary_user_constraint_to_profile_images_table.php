<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Virtual generated column: holds user_id when the row is a non-deleted
        // primary, NULL otherwise.  A unique index on this column allows multiple
        // NULLs (non-primary rows) but only one non-NULL value per user_id,
        // enforcing the "single primary photo" invariant at the database level.
        DB::statement("
            ALTER TABLE `profile_images`
            ADD COLUMN `primary_user_constraint` BIGINT UNSIGNED
            AS (CASE WHEN `is_primary` = 1 AND `deleted_at` IS NULL THEN `user_id` ELSE NULL END) VIRTUAL
        ");

        DB::statement("
            CREATE UNIQUE INDEX `uq_one_primary_per_user`
            ON `profile_images` (`primary_user_constraint`)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profile_images', function ($table) {
            $table->dropIndex('uq_one_primary_per_user');
            $table->dropColumn('primary_user_constraint');
        });
    }
};
