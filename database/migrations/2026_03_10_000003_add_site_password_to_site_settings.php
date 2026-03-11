<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('site_settings') && ! Schema::hasColumn('site_settings', 'site_password')) {
            Schema::table('site_settings', function (Blueprint $table): void {
                $table->string('site_password')->nullable()->after('site_password_enabled');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('site_settings') && Schema::hasColumn('site_settings', 'site_password')) {
            Schema::table('site_settings', function (Blueprint $table): void {
                $table->dropColumn('site_password');
            });
        }
    }
};
