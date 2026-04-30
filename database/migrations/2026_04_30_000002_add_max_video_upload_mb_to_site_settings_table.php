<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('site_settings') && ! Schema::hasColumn('site_settings', 'max_video_upload_mb')) {
            Schema::table('site_settings', function (Blueprint $table): void {
                $table->unsignedInteger('max_video_upload_mb')->nullable()->default(100)->after('home_page_records');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('site_settings') && Schema::hasColumn('site_settings', 'max_video_upload_mb')) {
            Schema::table('site_settings', function (Blueprint $table): void {
                $table->dropColumn('max_video_upload_mb');
            });
        }
    }
};
