<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('header_widgets', function (Blueprint $table): void {
            $table->string('header_background_color')->nullable()->after('logo_max_height');
            $table->unsignedInteger('header_height')->nullable()->after('header_background_color');
            $table->unsignedInteger('header_width')->nullable()->after('header_height');
        });

        Schema::table('footer_widgets', function (Blueprint $table): void {
            $table->string('footer_background_color')->nullable()->after('facebook_url');
            $table->unsignedInteger('footer_height')->nullable()->after('footer_background_color');
            $table->unsignedInteger('footer_width')->nullable()->after('footer_height');
        });
    }

    public function down(): void
    {
        Schema::table('header_widgets', function (Blueprint $table): void {
            $table->dropColumn([
                'header_background_color',
                'header_height',
                'header_width',
            ]);
        });

        Schema::table('footer_widgets', function (Blueprint $table): void {
            $table->dropColumn([
                'footer_background_color',
                'footer_height',
                'footer_width',
            ]);
        });
    }
};
