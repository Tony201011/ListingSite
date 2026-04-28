<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('site_settings') && Schema::hasColumn('site_settings', 'site_password')) {
            Schema::table('site_settings', function (Blueprint $table): void {
                $table->text('site_password')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('site_settings') && Schema::hasColumn('site_settings', 'site_password')) {
            Schema::table('site_settings', function (Blueprint $table): void {
                $table->string('site_password')->nullable()->change();
            });
        }
    }
};
