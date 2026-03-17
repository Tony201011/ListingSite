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
        Schema::create('hide_show_profiles', function (Blueprint $table) {
             $table->foreignId('user_id')->constrained()->cascadeOnDelete();
             $table->enum('status', ['hide', 'show'])->default('show');
             $table->timestamps();
             $table->unique('user_id'); // prevent duplicates per user
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hide_show_profiles');
    }
};
