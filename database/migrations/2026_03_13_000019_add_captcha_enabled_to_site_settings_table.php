<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('site_settings') && ! Schema::hasColumn('site_settings', 'captcha_enabled')) {
            Schema::table('site_settings', function (Blueprint $table): void {
                $table->boolean('captcha_enabled')->default(true)->after('enable_cookies');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('site_settings') && Schema::hasColumn('site_settings', 'captcha_enabled')) {
            Schema::table('site_settings', function (Blueprint $table): void {
                $table->dropColumn('captcha_enabled');
            });
        }
    }
};
