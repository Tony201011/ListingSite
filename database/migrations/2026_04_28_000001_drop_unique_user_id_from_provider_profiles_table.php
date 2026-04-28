<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // Remove duplicate provider profiles before restoring the unique constraint,
        // keeping only the earliest profile (lowest id) per user.
        DB::statement('
            DELETE FROM provider_profiles
            WHERE id NOT IN (
                SELECT min_id FROM (
                    SELECT MIN(id) AS min_id FROM provider_profiles GROUP BY user_id
                ) AS deduplicated
            )
        ');

        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->unique('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
