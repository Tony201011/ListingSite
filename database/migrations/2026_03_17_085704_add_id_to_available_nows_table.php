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
        Schema::table('available_nows', function (Blueprint $table) {
            $table->id()->first(); // adds an auto-incrementing primary key at the start
        });
    }

    public function down(): void
    {
        Schema::table('available_nows', function (Blueprint $table) {
            $table->dropColumn('id');
        });
    }
};
