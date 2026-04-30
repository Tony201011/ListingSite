<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profile_views', function (Blueprint $table) {
            $table->unsignedBigInteger('provider_profile_id')->nullable()->after('user_id');
        });

        // Best-effort backfill for MySQL: assign existing view records to the earliest
        // (minimum id) profile for each user.  This is an approximation – since the
        // original schema stored only user_id we cannot determine which specific profile
        // was actually viewed.  Historical popularity counts will therefore start from
        // each user's first profile; all new views will be attributed correctly.
        // SQLite (used in testing) starts with an empty profile_views table, so no
        // backfill is required there.
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('
                UPDATE profile_views
                SET provider_profile_id = (
                    SELECT MIN(id)
                    FROM provider_profiles
                    WHERE provider_profiles.user_id = profile_views.user_id
                )
                WHERE provider_profile_id IS NULL
            ');
        }

        Schema::table('profile_views', function (Blueprint $table) {
            $table->foreign('provider_profile_id')
                ->references('id')
                ->on('provider_profiles')
                ->cascadeOnDelete();
            $table->index(['provider_profile_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('profile_views', function (Blueprint $table) {
            $table->dropForeign(['provider_profile_id']);
            $table->dropIndex(['provider_profile_id', 'created_at']);
            $table->dropColumn('provider_profile_id');
        });
    }
};
