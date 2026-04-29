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
        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->string('hold_reason')->nullable()->after('profile_status');
            $table->timestamp('anonymized_at')->nullable()->after('hold_reason');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['hold_reason', 'anonymized_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('hold_reason')->nullable();
            $table->timestamp('anonymized_at')->nullable();
        });

        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->dropColumn(['hold_reason', 'anonymized_at']);
        });
    }
};
