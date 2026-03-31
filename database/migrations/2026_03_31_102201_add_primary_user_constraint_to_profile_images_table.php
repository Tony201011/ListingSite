<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('profile_images', function (Blueprint $table) {
            // Generated column: holds user_id when is_primary=true and row is
            // not soft-deleted, NULL otherwise.  MySQL unique indexes allow
            // multiple NULLs, so only one non-deleted primary per user is
            // permitted at the database level.
            $table->unsignedBigInteger('primary_user_constraint')
                ->nullable()
                ->storedAs('CASE WHEN is_primary = 1 AND deleted_at IS NULL THEN user_id ELSE NULL END')
                ->after('is_primary');

            $table->unique('primary_user_constraint', 'uq_one_primary_per_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profile_images', function (Blueprint $table) {
            $table->dropUnique('uq_one_primary_per_user');
            $table->dropColumn('primary_user_constraint');
        });
    }
};
