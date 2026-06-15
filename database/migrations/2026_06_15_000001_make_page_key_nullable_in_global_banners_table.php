<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('global_banners', function (Blueprint $table) {
            $table->string('page_key')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('global_banners', function (Blueprint $table) {
            $table->string('page_key')->nullable(false)->change();
        });
    }
};
