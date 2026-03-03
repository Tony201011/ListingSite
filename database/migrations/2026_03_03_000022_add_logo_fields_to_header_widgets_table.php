<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('header_widgets', function (Blueprint $table): void {
            $table->string('logo_type')->default('text')->after('id');
            $table->string('logo_path')->nullable()->after('logo_type');
        });
    }

    public function down(): void
    {
        Schema::table('header_widgets', function (Blueprint $table): void {
            $table->dropColumn(['logo_type', 'logo_path']);
        });
    }
};
