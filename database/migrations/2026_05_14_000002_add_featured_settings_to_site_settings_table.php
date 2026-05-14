<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->unsignedInteger('featured_credit_cost')->default(5)->after('available_now_duration_minutes');
            $table->unsignedInteger('featured_duration_days')->default(7)->after('featured_credit_cost');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['featured_credit_cost', 'featured_duration_days']);
        });
    }
};
