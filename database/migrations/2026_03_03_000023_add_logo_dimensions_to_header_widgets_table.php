<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('header_widgets', function (Blueprint $table): void {
            $table->unsignedInteger('logo_max_width')->nullable()->after('logo_path');
            $table->unsignedInteger('logo_max_height')->nullable()->after('logo_max_width');
        });
    }

    public function down(): void
    {
        Schema::table('header_widgets', function (Blueprint $table): void {
            $table->dropColumn(['logo_max_width', 'logo_max_height']);
        });
    }
};
