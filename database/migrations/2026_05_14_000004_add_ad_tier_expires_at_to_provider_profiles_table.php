<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table) {
            // $3/day – profile floats to top of home page grid (even when not online)
            $table->timestamp('home_featured_expires_at')->nullable()->after('free_listing_expires_at');
            // $2/day – profile shown in a banner strip on the local (state) page
            $table->timestamp('local_banner_expires_at')->nullable()->after('home_featured_expires_at');
            // $5/day – profile shown in a banner strip at the top of the home page (national)
            $table->timestamp('home_banner_expires_at')->nullable()->after('local_banner_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->dropColumn(['home_featured_expires_at', 'local_banner_expires_at', 'home_banner_expires_at']);
        });
    }
};
