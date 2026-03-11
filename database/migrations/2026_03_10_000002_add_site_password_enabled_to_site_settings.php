<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('site_settings') && ! Schema::hasColumn('site_settings', 'site_password_enabled')) {
            Schema::table('site_settings', function (Blueprint $table): void {
                $table->boolean('site_password_enabled')->default(false)->after('cookies_text');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('site_settings') && Schema::hasColumn('site_settings', 'site_password_enabled')) {
            Schema::table('site_settings', function (Blueprint $table): void {
                $table->dropColumn('site_password_enabled');
            });
        }
    }
};
