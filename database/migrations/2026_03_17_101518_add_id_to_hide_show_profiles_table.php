<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hide_show_profiles', function (Blueprint $table) {
            $table->id()->first(); // Add an auto-incrementing 'id' column as the first column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hide_show_profiles', function (Blueprint $table) {
            $table->dropColumn('id');
        });
    }
};
