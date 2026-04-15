<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->boolean('fatal_error_page_enabled')->default(false)->after('available_now_duration_minutes');
            $table->text('fatal_error_default_message')->nullable()->after('fatal_error_page_enabled');
            $table->string('fatal_error_query_param')->nullable()->after('fatal_error_default_message');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'fatal_error_page_enabled',
                'fatal_error_default_message',
                'fatal_error_query_param',
            ]);
        });
    }
};
