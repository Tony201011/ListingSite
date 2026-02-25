<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Removed redundant softDeletes, already exists from create migration
        // Schema::table('categories', function (Blueprint $table) {
        //     $table->softDeletes();
        // });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
