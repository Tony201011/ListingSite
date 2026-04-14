<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->unsignedInteger('online_status_max_uses')->default(4)->after('home_page_records');
            $table->unsignedInteger('online_status_duration_minutes')->default(60)->after('online_status_max_uses');
            $table->unsignedInteger('available_now_max_uses')->default(2)->after('online_status_duration_minutes');
            $table->unsignedInteger('available_now_duration_minutes')->default(120)->after('available_now_max_uses');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'online_status_max_uses',
                'online_status_duration_minutes',
                'available_now_max_uses',
                'available_now_duration_minutes',
            ]);
        });
    }
};
