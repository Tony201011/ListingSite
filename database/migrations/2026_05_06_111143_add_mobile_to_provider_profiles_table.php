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
            if (! Schema::hasColumn('provider_profiles', 'mobile')) {
                $table->string('mobile', 30)->nullable()->after('phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('provider_profiles', 'mobile')) {
                $table->dropColumn('mobile');
            }
        });
    }
};
