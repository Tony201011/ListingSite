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
        if (! Schema::hasTable('provider_profiles')) {
            return;
        }

        Schema::table('provider_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('provider_profiles', 'user_id')) {
                $table->foreignId('user_id')
                    ->after('id')
                    ->constrained()
                    ->cascadeOnDelete();

                // Ensure one-to-one between user and provider profile.
                $table->unique('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('provider_profiles') || ! Schema::hasColumn('provider_profiles', 'user_id')) {
            return;
        }

        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
