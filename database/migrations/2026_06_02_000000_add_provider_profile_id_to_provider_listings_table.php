<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('provider_listings', function (Blueprint $table) {
            $table->foreignId('provider_profile_id')->nullable()->constrained('provider_profiles')->cascadeOnDelete()->after('user_id');
        });

        // Backfill existing listings to the user's first provider profile (if any)
        DB::statement(<<<'SQL'
            UPDATE provider_listings
            SET provider_profile_id = (
                SELECT id FROM provider_profiles
                WHERE provider_profiles.user_id = provider_listings.user_id
                ORDER BY id LIMIT 1
            )
            WHERE provider_profile_id IS NULL
        SQL
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('provider_listings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('provider_profile_id');
        });
    }
};
