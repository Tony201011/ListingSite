<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('footer_widgets', function (Blueprint $table): void {
            $table->boolean('enable_brand_widget')->default(true)->after('facebook_url');
            $table->boolean('enable_navigation_widget')->default(true)->after('enable_brand_widget');
            $table->boolean('enable_advertisers_widget')->default(true)->after('enable_navigation_widget');
            $table->boolean('enable_legal_widget')->default(true)->after('enable_advertisers_widget');
        });
    }

    public function down(): void
    {
        Schema::table('footer_widgets', function (Blueprint $table): void {
            $table->dropColumn([
                'enable_brand_widget',
                'enable_navigation_widget',
                'enable_advertisers_widget',
                'enable_legal_widget',
            ]);
        });
    }
};
