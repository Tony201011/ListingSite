<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('about_us_pages', function (Blueprint $table): void {
            $table->string('banner_title')->nullable()->after('title');
            $table->string('banner_subtitle')->nullable()->after('banner_title');
            $table->string('banner_image_path')->nullable()->after('banner_subtitle');
        });
    }

    public function down(): void
    {
        Schema::table('about_us_pages', function (Blueprint $table): void {
            $table->dropColumn([
                'banner_title',
                'banner_subtitle',
                'banner_image_path',
            ]);
        });
    }
};
