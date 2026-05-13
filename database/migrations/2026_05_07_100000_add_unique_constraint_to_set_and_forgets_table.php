<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove duplicate rows (keep the most recently updated one per profile)
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('
                DELETE s1 FROM set_and_forgets s1
                INNER JOIN set_and_forgets s2
                ON s1.provider_profile_id = s2.provider_profile_id
                AND s1.id < s2.id
            ');
        } else {
            DB::statement('
                DELETE FROM set_and_forgets
                WHERE id NOT IN (
                    SELECT MAX(id) FROM set_and_forgets GROUP BY provider_profile_id
                )
                AND provider_profile_id IS NOT NULL
            ');
        }

        Schema::table('set_and_forgets', function (Blueprint $table) {
            $table->unique('provider_profile_id');
        });
    }

    public function down(): void
    {
        Schema::table('set_and_forgets', function (Blueprint $table) {
            $table->dropUnique(['provider_profile_id']);
        });
    }
};
