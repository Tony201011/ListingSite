<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add suburb to provider_profiles
        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->string('suburb', 255)->nullable()->after('city_id');
        });

        // Migrate existing data: copy user.suburb to all of their provider_profiles
        if (Schema::hasColumn('users', 'suburb')) {
            DB::statement('
                UPDATE provider_profiles
                SET suburb = (
                    SELECT u.suburb
                    FROM users u
                    WHERE u.id = provider_profiles.user_id
                      AND u.suburb IS NOT NULL
                      AND u.suburb != \'\'
                )
                WHERE EXISTS (
                    SELECT 1
                    FROM users u
                    WHERE u.id = provider_profiles.user_id
                      AND u.suburb IS NOT NULL
                      AND u.suburb != \'\'
                )
            ');
        }

        // Remove suburb from users
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('suburb');
        });
    }

    public function down(): void
    {
        // Re-add suburb to users
        Schema::table('users', function (Blueprint $table) {
            $table->string('suburb', 255)->nullable()->after('mobile');
        });

        // Migrate data back: copy suburb from the first profile back to user
        DB::statement('
            UPDATE users
            SET suburb = (
                SELECT pp.suburb
                FROM provider_profiles pp
                WHERE pp.user_id = users.id
                  AND pp.suburb IS NOT NULL
                  AND pp.suburb != \'\'
                ORDER BY pp.id ASC
                LIMIT 1
            )
            WHERE EXISTS (
                SELECT 1
                FROM provider_profiles pp
                WHERE pp.user_id = users.id
                  AND pp.suburb IS NOT NULL
                  AND pp.suburb != \'\'
            )
        ');

        // Remove suburb from provider_profiles
        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->dropColumn('suburb');
        });
    }
};
