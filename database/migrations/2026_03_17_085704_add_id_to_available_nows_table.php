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
        // SQLite does not support adding a PRIMARY KEY to an existing table,
        // so we recreate the table with the id column included.
        Schema::drop('available_nows');

        Schema::create('available_nows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['online', 'offline'])->default('offline');
            $table->timestamps();
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::drop('available_nows');

        Schema::create('available_nows', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['online', 'offline'])->default('offline');
            $table->timestamps();
            $table->unique('user_id');
        });
    }
};
