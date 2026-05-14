<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            // Number of free days for new listings before daily listing fee kicks in
            $table->unsignedInteger('free_listing_days')->default(21)->after('featured_duration_days');
            // Daily credit cost for home-page banner ad (national, $5/day)
            $table->unsignedInteger('home_banner_credit_cost')->default(5)->after('free_listing_days');
            // Daily credit cost for home-page featured placement ($3/day)
            $table->unsignedInteger('home_featured_credit_cost')->default(3)->after('home_banner_credit_cost');
            // Daily credit cost for local (state) banner ad ($2/day)
            $table->unsignedInteger('local_banner_credit_cost')->default(2)->after('home_featured_credit_cost');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['free_listing_days', 'home_banner_credit_cost', 'home_featured_credit_cost', 'local_banner_credit_cost']);
        });
    }
};
